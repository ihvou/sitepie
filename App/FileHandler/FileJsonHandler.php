<?php

namespace App\FileHandler;

use App\Exceptions\CsvDataException;
use App\Exceptions\CsvException;

class FileJsonHandler{

    private $siteName;

    private $jsonFileName;

    private $data = null;

    private $header = null;

    private $footer = null;

    private $headerScript = null;

    private $bodyScript = null;

    private $customData = [];

    private $ads = [];

    private $posts = [];

    private $tags = [];

    public function __construct($siteName)
    {
        if(!file_exists(TMP_JSON_DIR.DIRECTORY_SEPARATOR.$siteName.'.json')){
            throw new \Exception('Can\'t find json temporary file');
        }
        $this->siteName = $siteName;
        $this->jsonFileName = $siteName.'.json';
        $this->data = json_decode(file_get_contents(TMP_JSON_DIR.DIRECTORY_SEPARATOR.$this->jsonFileName));
        if(!$this->data){
            throw new CsvException('Incorrect data in json temporary file');
        }
    }

    public function getSiteName()
    {
        return $this->siteName;
    }

    public function getHeader()
    {
        if($this->header){
            return $this->header;
        }
        $header = $this->getSingleItemByItemTypeAndSubtype('header','main', true);
        $header->tags = explode(',',$header->tags);
        $this->header = $header;
        return $this->header;
    }

    public function getHeaderScript()
    {
        if($this->headerScript){
            return $this->headerScript;
        }
        $headerScript = (array)$this->getSingleItemByItemTypeAndSubtype('header','script');
        $this->headerScript = ($headerScript) ? $headerScript['media'] : false;
        return $this->headerScript;
    }

    public function getBodyScript()
    {
        if($this->bodyScript){
            return $this->bodyScript;
        }
        $script = (array)$this->getSingleItemByItemTypeAndSubtype('body','script');
        $this->bodyScript = ($script) ? $script['media'] : false;
        return $this->bodyScript;
    }

    public function getCustomData() {
        if($this->customData){
            return $this->customData;
        }
        $customData = $this->getCollectionItemByItemType('custom_data');
        if(!$customData){
            return [];
        }
        foreach ($customData as $item) {
            $item = (array)$item;
            $this->customData[$item['sub_type']] = $item;
        }
        return $customData;
    }

    public function getFooter()
    {
        if($this->footer){
            return $this->footer;
        }
        $this->footer = $this->getSingleItemByItemType('footer');
        return $this->footer;
    }

    public function getAds()
    {
        if(!$this->ads){
            return $this->ads;
        }
        $this->ads = $this->getCollectionItemByItemType('ad');
        return $this->ads;
    }

    public function getPosts()
    {
        if($this->posts){
            return $this->posts;
        }
        $this->posts = $this->getCollectionItemByItemType('post');
        foreach ($this->posts as &$postItem){
            switch ($postItem->sub_type) {
                case 'image':
                    $postItem->isImage = true;
                    break;
                case 'video':
                    $postItem->isVideo = true;
                    break;
                case 'gif':
                    $postItem->isGif = true;
                    break;
                case 'article':
                    $postItem->isArticle = true;
                    break;
                case 'gallery':
                    $postItem->isGallery = true;
                    break;
            }
            if($postItem->media){
                $postItem->media = explode(',',$postItem->media);
            }

            $postItem->tags = array_map(function($tag){
                return strtolower(preg_replace('/\s+/','-',trim($tag)));
            },explode(',',$postItem->tags));

            $postItem->postLink = 'post-'.str_replace(' ','-',strtolower(preg_replace("/[^ \w]+/", "",$postItem->title))).'.html';
        }
        return $this->posts;
    }

    public function getHeaderTags()
    {
        if($this->tags){
            return $this->tags;
        }
        $tags = array_map(function($tag){
            return strtolower(preg_replace('/\s+/','-',trim($tag)));
        },$this->getHeader()->tags);
        if(!$tags){
            throw new CsvException("There is no tags in 'header' row in your csv file");
        }
        $this->tags = $tags;
        return $this->tags;
    }

    public function getPostsTags(){
        $output = [];
        foreach ($this->getPosts() as $post){
            if(!$post->tags){
                continue;
            }
            foreach ($post->tags as $tag){
                if(!in_array($tag,$output) && $tag){
                    $output[] = $tag;
                }
            }
        }
        return $output;
    }

    public function getPostsByTag($tag){
        $tag = strtolower(preg_replace('/\s+/','-',trim($tag)));
        $output = [];
        foreach ($this->getPosts() as $post) {
            if(in_array($tag,$post->tags)){
                $output[] = $post;
            }
        }
        return $output;
    }

    public function getIndexPageAds(){
        $output = [];
        foreach($this->data as &$item){
            if(str_replace('_',' ', trim($item->sub_type)) == 'index page ad' && strtolower($item->status) == 'on'){
                $item->isAds = true;
                $output[] = $item;
            }
        }
        return $output;
    }

    public function getPostPageAds(){
        $output = [];
        foreach($this->data as $item){
            if(str_replace('_',' ', trim($item->sub_type)) == 'post page ad' && strtolower($item->status) == 'on'){
                $output[] = $item;
            }
        }
        return $output;
    }

    private function getSingleItemByItemType($itemType)
    {
        foreach($this->data as $item){
            if($item->item_type == $itemType && strtolower($item->status) == 'on'){
                return $item;
            }
        }
        throw new \Exception("There is no row with item_type = '$itemType' in your config");
    }

    private function getSingleItemByItemTypeAndSubtype($itemType,$subType, $checkStatus = false)
    {
        foreach($this->data as $item){
            if($item->item_type == $itemType && $item->sub_type == $subType){
                if($checkStatus && $item->status !== 'on'){
                    break;
                }
                return $item;
            }
        }
        throw new CsvDataException("There is no row with item_type = '$itemType' and sub_type = '$subType' in your config");
    }

    private function getCollectionItemByItemType($itemType)
    {
        $output = [];
        foreach($this->data as $item){
            if($item->item_type == $itemType && strtolower($item->status) == 'on'){
                $output[] = $item;
            }
        }
        if(!$output) {
            throw new CsvException("There is no rows with item_type = '$itemType' in your config");
        }
        return $output;
    }
}
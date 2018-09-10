<?php

namespace App\FileHandler;

class Render{

    private $fileJsonHandler;
    private $template;

    public function __construct(FileJsonHandler $fileJsonHandler, $template)
    {
        $this->fileJsonHandler = $fileJsonHandler;
        $this->template = $template;
    }

    public function renderIndexPages()
    {
        $posts = $this->fileJsonHandler->getPosts();
        $ads = $this->fileJsonHandler->getIndexPageAds();

        $indexPagesCount = $this->getPagesCount($posts);
        $renderData['header'] = (array)$this->fileJsonHandler->getHeader();
        $renderData['header_tags'] = (array)$this->fileJsonHandler->getHeaderTags();
        $renderData['posts_tags'] = $this->fileJsonHandler->getPostsTags();
        $renderData['header_script'] = $this->fileJsonHandler->getHeaderScript();
        $renderData['body_script'] = $this->fileJsonHandler->getBodyScript();
        $renderData['footer'] = $this->fileJsonHandler->getFooter();
        $renderData['total_pages'] = $indexPagesCount;

        for($i = 1; $i <= $indexPagesCount; $i++){
            $mustache = MustacheHandler::getRenderMustacheObject($this->template);
            $renderData['page_number'] = $i;
            $postsPart = array_slice($posts,($i-1)*POSTS_PER_PAGE,POSTS_PER_PAGE);
            $renderData['posts'] = $this->insertAdsRandomly($postsPart,$ads);

            if($indexPagesCount == 1){
                $renderData['pagination'] = [
                    'next_page' => false,
                    'previous_page' => false
                ];
            }

            if($i == 1){
                $renderData['pagination'] = [
                    'next_page' => ($indexPagesCount > 1) ? 'page2.html' : false,
                    'previous_page' => false
                ];
            }elseif ($i == $indexPagesCount){
                $renderData['pagination'] = [
                    'next_page' => false,
                    'previous_page' => ($i == 2) ? 'index.html' : 'page'.($i-1).'.html'
                ];
            }else{
                $renderData['pagination'] = [
                    'next_page' => 'page'.($i+1).'.html',
                    'previous_page' => ($i == 2) ? 'index.html' : 'page'.($i-1).'.html'
                ];
            }

            $outputFileName = ($i > 1) ? 'page'.$i.'.html' : 'index.html';
            $outputFullName = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->fileJsonHandler->getSiteName().DIRECTORY_SEPARATOR.$outputFileName;

            $tpl = $mustache->loadTemplate('index');
            file_put_contents($outputFullName,$tpl->render($renderData));
        }
    }

    public function renderTagsPages(){
        $tagsCollection = $this->fileJsonHandler->getPostsTags();
        foreach ($tagsCollection as $tag) {
            $this->renderTagItemPages($tag);
        }
    }

    private function renderTagItemPages($tag){

        $posts = $this->fileJsonHandler->getPostsByTag($tag);
        $ads = $this->fileJsonHandler->getIndexPageAds();

        $pagesCount = $this->getPagesCount($posts);
        $renderData['header'] = (array)$this->fileJsonHandler->getHeader();
        $renderData['header_tags'] = (array)$this->fileJsonHandler->getHeaderTags();
        $renderData['header_script'] = $this->fileJsonHandler->getHeaderScript();
        $renderData['body_script'] = $this->fileJsonHandler->getBodyScript();
        $renderData['footer'] = $this->fileJsonHandler->getFooter();
        $renderData['posts_tags'] = $this->fileJsonHandler->getPostsTags();
        $renderData['total_pages'] = $pagesCount;
        $renderData['tag'] = $tag;

        for($i = 1; $i <= $pagesCount; $i++){
            $mustache = MustacheHandler::getRenderMustacheObject($this->template);
            $renderData['page_number'] = $i;
            $postsPart = array_slice($posts,($i-1)*POSTS_PER_PAGE,POSTS_PER_PAGE);
            $renderData['posts'] = $this->insertAdsRandomly($postsPart,$ads);

            if($pagesCount == 1){
                $renderData['pagination'] = [
                    'next_page' => false,
                    'previous_page' => false
                ];
            }

            if($i == 1){
                $renderData['pagination'] = [
                    'next_page' => ($posts > 1) ? $tag.'2.html' : false,
                    'previous_page' => false
                ];
            }elseif ($i == $pagesCount){
                $renderData['pagination'] = [
                    'next_page' => false,
                    'previous_page' => ($i == 2) ? $tag.'.html' : $tag.($i-1).'.html'
                ];
            }else{
                $renderData['pagination'] = [
                    'next_page' => $tag.($i+1).'.html',
                    'previous_page' => ($i == 2) ? $tag.'.html' : $tag.($i-1).'.html'
                ];
            }

            $outputFileName = ($i > 1) ? $tag.$i.'.html' : $tag.'.html';
            $outputFullName = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->fileJsonHandler->getSiteName().DIRECTORY_SEPARATOR.$outputFileName;

            $tpl = $mustache->loadTemplate('tag');
            file_put_contents($outputFullName,$tpl->render($renderData));
        }

    }

    public function renderPostsPages(){
        $posts = $this->fileJsonHandler->getPosts();
        $renderData['ads'] = $this->fileJsonHandler->getPostPageAds();
        $renderData['header'] = (array)$this->fileJsonHandler->getHeader();
        $renderData['header_tags'] = (array)$this->fileJsonHandler->getHeaderTags();
        $renderData['posts_tags'] = $this->fileJsonHandler->getPostsTags();
        $renderData['header_script'] = $this->fileJsonHandler->getHeaderScript();
        $renderData['body_script'] = $this->fileJsonHandler->getBodyScript();
        $renderData['footer'] = $this->fileJsonHandler->getFooter();

        foreach ($posts as $post){
            $mustache = MustacheHandler::getRenderMustacheObject($this->template);
            $renderData['post'] = (array)$post;

            $outputFileName = $post->postLink;
            $outputFullName = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->fileJsonHandler->getSiteName().DIRECTORY_SEPARATOR.$outputFileName;

            $tpl = $mustache->loadTemplate('post');
            file_put_contents($outputFullName,$tpl->render($renderData));
        }
    }

    public function copyAssets(){
        $source = INPUT_TEMPLATES_DIR.DIRECTORY_SEPARATOR.$this->template.DIRECTORY_SEPARATOR.'assets';
        $dest = HTML_OUTPUT_DIR.DIRECTORY_SEPARATOR.$this->fileJsonHandler->getSiteName().DIRECTORY_SEPARATOR.'assets';
        mkdir($dest, 0777, TRUE);

        foreach(
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if( $item->isDir()){
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), 0777, TRUE);
            }
            else{
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    private function getPagesCount($data)
    {
        return ceil(count($data)/POSTS_PER_PAGE);
    }

    private function insertAdsRandomly($posts,$ads){
        foreach ($ads as $ad) {
            array_splice($posts, rand(0, count($posts)), 0, [$ad]);
        }
        return $posts;
    }

}
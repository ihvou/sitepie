<?php
/**
 * Created by PhpStorm.
 * User: abezgliadnov
 * Date: 6/3/18
 * Time: 5:49 PM
 */

namespace Web\Google;

class GoogleSpreadsheet
{

    private $client = null;
    private $siteName;
    private $sheetId;

    public function __construct($siteName,$sheetId)
    {
        $this->sheetId = $sheetId;
        $this->siteName = $siteName;
        $this->initClient();
    }

    private function getCredentialsFilePath(){
        return CREDENTIALS_DIR.DIRECTORY_SEPARATOR.$this->siteName.'_'.$this->sheetId.'cred.json';
    }

    private function initClient()
    {
        if(!$this->client) {
            $this->client = new \Google_Client();
            $this->client->setApplicationName(GOOGLE_APP_NAME);
            $this->client->setScopes(\Google_Service_Sheets::SPREADSHEETS_READONLY);
            $this->client->setAuthConfig(ROOT_DIR . DIRECTORY_SEPARATOR . 'client_secret.json');
            $this->client->setAccessType('offline');
            $this->client->setApprovalPrompt("force");
        }

    }

    private function setAccessTokenFromFile(){
        $accessToken = json_decode(file_get_contents($this->getCredentialsFilePath()), true);
        $this->client->setAccessToken($accessToken);
    }

    public function OAuthGetCredentals()
    {
        // Load previously authorized credentials from a file.
        if (file_exists($this->getCredentialsFilePath())) {
            $this->setAccessTokenFromFile();
        } else {
            // Request authorization from the user.
            $authUrl = $this->client->createAuthUrl();
            header("Location: $authUrl", true, 302);
            exit;
        }
        $this->refreshToken();
    }

    public function setNewAccessTokenFromGoogle($authCode){
        // Exchange authorization code for an access token.
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        file_put_contents($this->getCredentialsFilePath(), json_encode($accessToken));

        $this->client->setAccessToken($accessToken);
        $this->refreshToken();
    }

    private function refreshToken(){
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            file_put_contents($this->getCredentialsFilePath(), json_encode($this->client->getAccessToken()));
        }
    }

    private function getSpreadsheetInfo($sheetId){
        $service = new \Google_Service_Sheets($this->client);
        $response = $service->spreadsheets_values->get($sheetId,GOOGLE_INFO_SHEET_NAME.SPREADSHEET_RANGE);
        $values = $response->getValues();
        return $values;
    }

    public function getSpreadsheetData(){
        $this->setAccessTokenFromFile();
        $data = $this->getSpreadsheetInfo($this->sheetId);
        return $data;
    }
}
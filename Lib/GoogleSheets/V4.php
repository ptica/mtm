<?php
// define APP constant for cli invocation
defined('APP') or define('APP', dirname(dirname(__DIR__)).'/');

require_once APP . '/Vendor/autoload.php';

define('APPLICATION_NAME',  'Google Sheets API PHP Quickstart');
define('CREDENTIALS_PATH',   APP . 'Config/google_sheets/sheets.googleapis.com-php-credentials.json');
define('CLIENT_SECRET_PATH', APP . 'Config/google_sheets/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at CREDENTIALS_PATH
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS_READONLY)
));

class V4 {
    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    public function getClient() {
      $client = new Google_Client();
      $client->setApplicationName(APPLICATION_NAME);
      $client->setScopes(SCOPES);
      $client->setAuthConfig(CLIENT_SECRET_PATH);
      $client->setAccessType('offline');

      // Load previously authorized credentials from a file.
      if (file_exists(CREDENTIALS_PATH)) {
        $accessToken = json_decode(file_get_contents(CREDENTIALS_PATH), true);
      } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if(!file_exists(dirname(CREDENTIALS_PATH))) {
          mkdir(dirname(CREDENTIALS_PATH), 0700, true);
        }
        file_put_contents(CREDENTIALS_PATH, json_encode($accessToken));
        printf("Credentials saved to %s\n", CREDENTIALS_PATH);
      }
      $client->setAccessToken($accessToken);

      // Refresh the token if it's expired.
      if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents(CREDENTIALS_PATH, json_encode($client->getAccessToken()));
      }
      return $client;
    }

    public function getData($spreadsheetId, $range) {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        if (count($values) == 0) {
          print "No data found.\n";
        }
        $data = [];
        foreach ($values as $row) {
            $eur = (int) array_pop($row);
            $czk = (int) array_pop($row);
            $row = array_filter($row, function ($v) { return $v != 'na'; } );
            sort($row);
            $key = implode('-', $row);
            $data[$key] = compact('czk', 'eur');
        }

        $data = json_encode($data, JSON_PRETTY_PRINT);
        return $data;
    }

    public function saveData($filename, $spreadsheetId, $range) {
        $data = $this->getData($spreadsheetId, $range);
        if (file_put_contents($filename, $data) === false) {
            return "Error writting prices.\n";
        } else {
            return "$filename written ok\n";
        }
    }
}

// called from command line to authenticate and store the data
if (php_sapi_name() == 'cli') {
    $v4 = new V4();
    $spreadsheetId = '1D2OpAdVu01xhcbVqQv3N9YVg4FpSAPbIw5_igi-JsBQ';
    echo $v4->saveData(APP . 'Config/price-tlt.json', $spreadsheetId, $range = 'TLT!A2:D');
    echo $v4->saveData(APP . 'Config/price-workshop.json', $spreadsheetId, $range = 'Workshop!A2:C');
}

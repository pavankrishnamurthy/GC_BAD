<?php

require '../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

$CLIENT_ID = "fe5ab65e-8b11-428b-a9fd-21bbdd65d279";
$CLIENT_SECRET = "d14a8af24656aae45a57355fa6ab638fd0615c10596ead4fb55673313f53e98d1c9f9699bce21631033f78cdff16db7669ce9f55a28afc3f5409221a5abcc89f";
$INSTITUION_ID = "SANDBOXFINANCE_SFIN0000";
$REQUESITION_ID = "05c80009-5538-4220-b2cf-26afadd3502e";
$BANK_ACCOUNT_ID = "b9751bf0-7c3a-4a9c-bd33-d633f9f6c599";

class GCBAD {
    private $accessToken;
    private $bankAccountId;
    private $clientID;
    private $clientSecret;


    public function __construct($clientId, $clientSecret, $bankAccountId) {
        $this->clientID = $clientId;
        $this->clientSecret = $clientSecret;
        $this->bankAccountId = $bankAccountId; // Set the bank account ID
    }

    public function getAccessToken() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,"https://bankaccountdata.gocardless.com/api/v2/token/new/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
    
        $data = array('secret_id' => $this->clientID, 'secret_key' => $this->clientSecret);
        $data = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));
    
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
    
        if ($info['http_code'] == 200) {
            $responseJson = json_decode($response, true);
            $this->accessToken = $responseJson["access"];
        } else {
            echo "Error fetching access token: " . $response;
            return null;
        }
        return $this->accessToken;
    }

    public function getBankAccountId() {
        return $this->bankAccountId;
    }


    public function getTransactions($bankAccountId, $accessToken) {
        echo "<h3>Fetching transactions for bank account ID: {$bankAccountId}</h3>";
        //echo "<p>Using access token: {$accessToken}</p>";
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bankaccountdata.gocardless.com/api/v2/accounts/b9751bf0-7c3a-4a9c-bd33-d633f9f6c599/transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
              'Authorization: Bearer ' . $accessToken      
            ),
          ));
    
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
    
        if ($info['http_code'] == 200) {
            return json_decode($response, true) ['transactions']['booked'];
        } else {
            echo "Error fetching transactions: " . $response;
            return null;
        }
    }

    public function getAccountDetails($bankAccountId) {
    $client = new Client();
    $headers = [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                ];
    $request = new Request('GET', "https://bankaccountdata.gocardless.com/api/v2/accounts/{$bankAccountId}/details", $headers);
    $res = $client->sendAsync($request)->wait();
    return json_decode($res->getBody());
    }

    public function calculateTotalCredits($transactions) {
        $totalCredits = 0;
        foreach ($transactions as $transaction) {
            if (isset($transaction['creditorName'])) {
                // echo "<p>Transaction:  {$transaction['transactionAmount']['amount']}</p>";
                $totalCredits += $transaction['transactionAmount']['amount'];
            }
        }
        return $totalCredits;
    }
    public function calculateTotalDebits($transactions) {
        $totalDebits = 0;
        foreach ($transactions as $transaction) {
            if (isset($transaction['debtorName'])) {
                // echo "<p>Transaction:  {$transaction['transactionAmount']['amount']}</p>";
                $totalDebits += $transaction['transactionAmount']['amount'];
            }
        }
        return $totalDebits;
    }
    public function getBalance($transactions) {
        $totalCredits = $this->calculateTotalCredits($transactions);
        $totalDebits = $this->calculateTotalDebits($transactions);
        return $totalDebits + $totalCredits;
    }

}


$GCBADClient = new GCBAD($CLIENT_ID, $CLIENT_SECRET, $BANK_ACCOUNT_ID);

$accessToken = $GCBADClient->getAccessToken();
if ($accessToken) {
    echo "<h1>GoCardless Access Token</h1>";
    echo "<pre>";
    //print_r($accessToken);
    echo "</pre>";
} else {
    echo "Error fetching access token.";
}


$transactions = $GCBADClient->getTransactions($GCBADClient->getBankAccountId(), $accessToken);
if ($transactions) {
    echo "<h1>GoCardless Transactions</h1>";
    echo "<pre>";
    //print_r($transactions);
    echo "</pre>";
} else {
    echo "Error fetching transactions.";
}

$accountDetails = $GCBADClient->getAccountDetails($GCBADClient->getBankAccountId());
if ($accountDetails) {
    echo "<h1>GoCardless Account Details</h1>";
    echo "<pre>";
    print_r($accountDetails);
    echo "</pre>";
} else {
    echo "Error fetching account details.";
}

$totalCredits = $GCBADClient->calculateTotalCredits($transactions);
echo "<h2>Total Credits: {$totalCredits}</h2>";

$totalDebits = $GCBADClient->calculateTotalDebits($transactions);
echo "<h2>Total Debits: {$totalDebits}</h2>";

$balance = $GCBADClient->getBalance($transactions);
echo "<h2>Balance: {$balance}</h2>";

?>

<?php

use PHPUnit\Framework\TestCase;

require_once './src/bootstrap.php';
require_once '../src/index.php';



class GCBadTest extends TestCase {



    private $GCBADClient;
    
    public function setUp(): void {
        $CLIENT_ID = "fe5ab65e-8b11-428b-a9fd-21bbdd65d279";
        $CLIENT_SECRET = "d14a8af24656aae45a57355fa6ab638fd0615c10596ead4fb55673313f53e98d1c9f9699bce21631033f78cdff16db7669ce9f55a28afc3f5409221a5abcc89f";
        $BANK_ACCOUNT_ID = "b9751bf0-7c3a-4a9c-bd33-d633f9f6c599";
        $this->GCBADClient = new GCBAD($CLIENT_ID, $CLIENT_SECRET, $BANK_ACCOUNT_ID);
    }
    public function tearDown(): void {
        $this->GCBADClient = null; // Clean up the client after each test
    }


    public function testTransactions() {
        $accessToken = $this->GCBADClient->getAccessToken(); // Define the access token
        $bankAccountID = $this->GCBADClient->getBankAccountId(); // Get the bank account ID
        $transactions = $this->GCBADClient->getTransactions($bankAccountID, $accessToken);
        $this->assertNotEmpty($transactions, "Transactions should not be empty");
    }
    public function testTotalCredits() {
        $accessToken = $this->GCBADClient->getAccessToken(); // Define the access token
        $bankAccountID = $this->GCBADClient->getBankAccountId(); // Get the bank account ID
        $transactions = $this->GCBADClient->getTransactions($bankAccountID, $accessToken);
        $totalCredits = $this->GCBADClient->calculateTotalCredits($transactions);
        $this->assertEquals(-8296.2, $totalCredits, "Total credits should be non-negative");
    }
    public function testTotalDebits() {
        $accessToken = $this->GCBADClient->getAccessToken(); // Define the access token
        $bankAccountID = $this->GCBADClient->getBankAccountId(); // Get the bank account ID
        $transactions = $this->GCBADClient->getTransactions($bankAccountID, $accessToken);
        $totalDebits = $this->GCBADClient->calculateTotalDebits($transactions);
        $this->assertEquals(22161.3, $totalDebits, "Total debits should be non-negative");
    }
}


?>
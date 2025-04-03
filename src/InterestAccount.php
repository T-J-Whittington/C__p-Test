<?php

class InterestAccount {

    /**
     * Array of interest rates for different tiers of income.<br>
     * Includes a default interest rate for missing income data.
     * @param array(name array(rate, income))
     */
    private const INTEREST_RATES = [
        'default' => [
            'rate' => 0.5,
            'minimumIncome' => null
        ],
        'low' => [
            'rate' => 0.93,
            'minimumIncome' => 0
        ],
        'high' => [
            'rate' => 1.02,
            'minimumIncome' => 5000
        ]
    ];

    /**
     * User ID, in UUIDv4 format.
     * @param $userID String Nullable UUIDv4 format.
     */
    private ?string $userID = null;

    private bool $accountSet = false;

    public function getUserID() {
        return $this->userID;
    }

    public function setUserID(string $userID) {
        return $this->userID = $userID;
    }

    public function __construct($userID)
    {
        $this->userID = $userID;
        //TODO: Check for existing account.
    }

    /**
     * Create a new account associated to the current $userID.
     */
    public function newAccount(int $income) {
        //TODO: Create new account and handle checking for existing account.
        $interestRate = $this->getInterestRate($income);
    }

    /**
     * Get the appropriate interest rate for the current income.<br>
     * Otherwise uses the default interest rate.
     * @param int $income
     * @return float interest rate
     */
    private function getInterestRate(int $income) {
        $interestRate = array_find($this::INTEREST_RATES, function($tier, $income){
            return $tier['minimumIncome'] < $income;
        });
        return $interestRate ? $interestRate['rate'] : $this::INTEREST_RATES['default']['rate'];
    }

    /**
     * Validation for functions which require an active account.
     */
    private function accountRequired() {
        if(!$this->accountSet){
            // Handle some form of error for no in use account.
        }
    }

    /**
     * Deposit/add funds to the current savings account.
     * @param int $funds Funds to be deposited.
     * @return 
     */
    public function depositFunds(int $funds) {

    }

    public function calculateInterest() {

    }

    public function listTransactions() {

    }


}
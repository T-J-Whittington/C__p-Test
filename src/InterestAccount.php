<?php

namespace ChipTest;

class InterestAccount {

    //region Constants, Getters and Setters.

    /**
     * Stats API Parameters
     */
    private const STATS_API = [
        'url' => 'https://stats.dev.chip.test/',
        'paths' => [
            'users' => 'users/'
        ]
    ];

    /**
     * Array of interest rates for different tiers of income.
     * 
     * Includes a default interest rate for missing income data.
     * 
     * Also has daily/three day rates for normal years and leap years.
     * 
     * This is ordered from high to low.
     * @var array(name array(rate, income))
     */
    private const INTEREST_RATES = [
        'high' => [
            'rate' => 1.02,
            'dailyRate' => 0.0027945205, // /365
            'threeDayRate' => 0.0083835615, // *3
            'leapYearDailyRate' => 0.0027868852, // /366
            'leapYearThreeDayRate' => 0.0083606556, // *3
            'minimumIncome' => 500000 // £5000, but in pence.
        ],
        'low' => [
            'rate' => 0.93,
            'dailyRate' => 0.0025479452,
            'threeDayRate' => 0.0076438356,
            'leapYearDailyRate' => 0.0025409836,
            'leapYearThreeDayRate' => 0.0076229508,
            'minimumIncome' => 0
        ],
        'default' => [
            'rate' => 0.5,
            'dailyRate' => 0.0013698630, 
            'threeDayRate' => 0.004109589,
            'leapYearDailyRate' => 0.0013661202,
            'leapYearThreeDayRate' => 0.0040983607,
            'minimumIncome' => null
        ]
    ];

    /**
     * User ID, in UUIDv4 format.
     * @var null|string $userID UUIDv4 format.
     */
    private ?string $userID;

    public function getUserID() {
        return $this->userID;
    }

    public function setUserID(string $userID) {
        return $this->userID = $userID;
    }

    /**
     * User's income, in pence.
     * @var null|int $income
     */
    private ?int $income;

    public function getIncome() {
        return $this->income;
    }

    public function setIncome(?int $income) {
        $this->income = $income;
    }

    /**
     * User's interest rate, set on account creation.
     * @var null|float $interestRate
     */
    private ?float $interestRate;

    public function getInterestRate() {
        return $this->interestRate;
    }

    public function setInterestRate(float $interestRate) {
        $this->interestRate = $interestRate;
    }

    /**
     * User's current balance, in pence.
     *
     * @var integer|null
     */
    private ?int $balance;

    public function getBalance() {
        return $this->balance;
    }

    public function setBalance(int $balance) {
        $this->balance = $balance;
    }

    //endregion

    public function __construct()
    {
        // Set the default interest rate.
        $this->setInterestRate($this::INTEREST_RATES['default']['rate']);
    }

    /**
     * Create a new account associated to the current $userID.
     * @param string $userID In UUIDv4 format.
     */
    public function newAccount(string $userID) {
        // If the User ID is absent, return a failure.
        if(!$userID){
            throw new \Exception('No userID');
        }

        // Attempt to fetch a user's income via the endpoint.
        $this->handleUserResponse($this->statisticsAPIHandler('user', $userID, true));

        $this->setBalance(0);

        return [$this->getUserID(), $this->getInterestRate()];
    }
    
    /**
     * Set an account to be used by class functions.
     * @param array $account
     */
    public function setAccount($account) {
        // Attempt to get a userID via the endpoint.
        $this->setUserID($account['id']);
        $this->setIncome($account['income']);
        $this->setBalance($account['balance']);
        $this->setInterestRate($account['interestRate']);
    }

    /**
     * Get the currently set account details.
     *
     * @return void
     */
    public function getAccount() {
        return [
            $this->getUserID(),
            $this->getBalance(),
            $this->getInterestRate(),
            $this->getIncome()
        ];
    }

    /**
     * Handle incoming user account data from the API.
     */
    private function handleUserResponse($response) {
        if($response['statusCode'] !== 200) {
            throw new \Exception('Invalid Response - ' . $response['body']);
        }

        $accountDetails = json_decode($response['body']);

        // Set account details.
        $this->setUserID($accountDetails->id);
        $this->setIncome($accountDetails->income);

        $this->handleInterestRate();
    }

    /**
     * Set the appropriate interest rate for the current income.
     * 
     * Otherwise leaves the default interest rate.
     */
    private function handleInterestRate() {
        if($this->income){
            $this->setInterestRate(array_find($this::INTEREST_RATES, function($rate) {
                return $rate['minimumIncome'] !== null && $rate['minimumIncome'] <= $this->income;
            })['rate']);
        } else {
            $this->setInterestRate($this::INTEREST_RATES['default']['rate']);
        }
    }

    /**
     * Deposit/add funds to the current savings account.
     * @param int $funds Funds to be deposited.
     * @return $account
     */
    public function depositFunds(int $funds) {
        $this->balance += $funds;

        return $this->getAccount();
    }

    /**
     * Used by calculateInterest() to store any interest valued <1p, to be used on the next
     * applicable calculation.
     *
     * @var float
     */
    private float $delayedInterest = 0;

    /**
     * Override leap year. Null for normal leap year checking, boolean to force leap year or not.
     *
     * @var boolean|null
     */
    private ?bool $overrideLeapYear = null;

    /**
     * Calculate the interest on a given account, and deposit the new funds to the account.
     * 
     * Interest valued at <1p is not added immediately, and is instead added onto the next available calculation.
     * 
     * Interest is calculated once per three days.
     * 
     * Interest Rate is *per annum*, so it must be divided to match the amount. This is handled in constants.
     * 
     * @param $account Account to calculate interest on.
     * @param int $days Amount of time to calculate interest over, in days.
     * @param ?bool overrideLeapYear Override normal leap year checks for testing.
     * Null for normal leap year checking, boolean to force leap year or not.
     * @return $account Account with new amount
     */
    public function calculateInterest(int $days, ?bool $overrideLeapYear = null) {
        $this->delayedInterest = 0;
        $this->overrideLeapYear = $overrideLeapYear;

        $threeDayInterestRate = $this->deriveInterestRateFromAccountRate();

        for($x = 0; $x < intdiv($days, 3); $x++){
            $interest = 0;

            // Calculate interest.
            $interest = $this->balance * $threeDayInterestRate;

            // Apply delayed interest.
            $interest += $this->delayedInterest;
            if($interest > 1){
                // Round down partial pennies to 0.
                $interest = floor($interest);
                $this->balance += $interest;
                $this->logInterestTransaction($interest);
                $this->delayedInterest = 0;
            } else {
                $this->delayedInterest = $interest;
            }
        }

        return $this->getAccount();
    }

    /**
     * Derive the appropriate three day interest rate from the given account's interest rate.
     * 
     * This takes leap year into account, and can be overriden by setting the overrideLeapYear class variable.
     *
     * @return float
     */
    private function deriveInterestRateFromAccountRate(){
        $derivedConstantInterestRate = array_find($this::INTEREST_RATES, 
            fn($rate) => $rate['rate'] == $this->interestRate);

        if($this->overrideLeapYear === null) {
            $threeDayRate = date('L') ?
                $derivedConstantInterestRate['leapYearThreeDayRate'] :
                $derivedConstantInterestRate['threeDayRate'];
        } else {
            $threeDayRate = $this->overrideLeapYear ? 
                $derivedConstantInterestRate['leapYearThreeDayRate'] :
                $derivedConstantInterestRate['threeDayRate'];
        }

        return $threeDayRate;
    }

    private array $interestTransactions = [];

    /**
     * Log an interest transaction.
     *
     * @param int $interest
     * @return void
     */
    private function logInterestTransaction($interest) {
        array_push($this->interestTransactions, ['Interest', $interest]);
    }

    /**
     * List all of an account's transactions, including interest payments.
     * @param ?array $existingTransactions A list of the account's existing transactions.
     * @return array()
     */
    public function listTransactions($existingTransactions = []) {

        return array_push($existingTransactions, $this->interestTransactions);
    }

    /**
     * TODO: Update this once the Statistics API is in place. Currently simulates sending an API request.
     * 
     * Gather details from the statistics API.
     *
     * @param string $endpoint Mock API endpoint.
     * @param string|null $body Request body.
     * @return array Response with status code and JSON body
     */
    private function statisticsAPIHandler(string $endpoint, ?string $body = null, $new = false): array
    {
        $method = $new ? 'POST' : 'GET';

        // Select the right endpoint and set up details.
        switch($endpoint) {
            case 'user':
                $path = $this::STATS_API['paths']['users'];
                break;
        }

        $endpoint = $this::STATS_API['url'] . $path;

        // Add the body (userID) to the endpoint if we're using a GET request.
        if($method === 'GET' && $body) {
            $endpoint .= $body;
        }

        $request = [
            'method' => strtoupper($method),
            'endpoint' => $endpoint,
            'body' => $body ? json_encode($body, JSON_THROW_ON_ERROR) : null,
            'path' => $path //TODO: Remove once the API is in place.
        ];

        // TODO: Update with a real request once the statistics API is in place.
        // Simulate the request via the mock endpoint.
        return (new StatisticsEndpoint)->sendRequest($request);
    }
}
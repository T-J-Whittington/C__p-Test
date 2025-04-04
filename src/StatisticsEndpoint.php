<?php

namespace ChipTest;

/**
 * This class is purely to mock up the API endpoint and is not/will not be a part of 
 * the codebase once that endpoint is properly implemented.
 */
class StatisticsEndpoint {

    // Mock user accounts.
    private const USER_ACCOUNT_1 = [
        'id' => '332705c0-fadd-4663-ace4-6ea1c3297566',
        'active' => true,
        'interestRate' => 0.93,
        'income' => 200,
        'amount' => 200,
        'created' => ''
    ];

    private const USER_ACCOUNT_2 = [
        'id' => '3567bd11-03c5-44ad-ae5d-5021ac26d210',
        'active' => true,
        'interestRate' => 1.02,
        'income' => 6000,
        'amount' => 6000,
    ];

    private const USER_ACCOUNT_3 = [
        'id' => 'e0c1c412-823e-4af8-b256-2d1c22b3b376',
        'active' => true,
        'interestRate' => 0.5,
        'income' => null,
        'amount' => 0
    ];

    private const USER_ACCOUNT_4 = [
        'id' => '3d676dc7-ed2c-447c-8eb9-8cd8c5e86279',
        'active' => false,
        'income' => 999999,
        'amount' => 0
    ];

    private const USER_ACCOUNT_5 = [
        'id' => '46437aa0-2386-4c90-b789-8513a47fda27',
        'active' => true,
        'interestRate' => 0.93,
        'income' => 200,
        'amount' => 200,
    ];

    /**
     * Array that behaves as though these accounts already exist when probing user endpoint.
     */
    private array $existingUserAccounts;

    /**
     * Array for quickly "generating" a new user account.
     */
    private array $newUserAccounts;

    public function __construct()
    {
        $this->newUserAccounts = [
            $this::USER_ACCOUNT_1,
            $this::USER_ACCOUNT_2,
            $this::USER_ACCOUNT_3,
            $this::USER_ACCOUNT_4,
        ];
        $this->existingUserAccounts = [
            $this::USER_ACCOUNT_5,
        ];
    }

    /**
     * Function to generate an API-like response, with a statusCode and a body.
     */
    private function generateResponse($return) {
        return [
            'statusCode' => $return['code'],
            'body' => json_encode($return['body'])
        ];
    }

    /**
     * Receive a "request". Named so that it looks appropriate in the classes sending a mock request.
     * @param array $request[method, endpoint, body]
     * @return array(statusCode, body)
     */
    public function sendRequest($request) {

        print_r("Send Request: ");
        print_r($request);

        switch($request['path']){
            case 'users/':
                $function = $request['method'] == 'GET' ? 'createAccount' : 'getAccount';
        }

        print_r($function);

        return $this->generateResponse($this->$function($request['body']));
    }

    private function createAccount($userID) {
        if(array_any($this->existingUserAccounts, fn($account) => $account['id'] == $userID)){
            return(['code' => 100, 'body' => 'User Account exists.']);
        }
        return ['code' => 200, 'body' => array_find($this->newUserAccounts, fn($account) => $account['id'] == $userID)];
    }

    private function getAccount($userID) {
        if(array_any($this->existingUserAccounts, fn($account) => $account['id'] == $userID)){
            return ['code' => 200, 'body' =>  array_find($this->newUserAccounts, fn($account) => $account['id'] == $userID)];
        }
        return ['code' => 404, 'body' =>  'Account not found'];
    }

}
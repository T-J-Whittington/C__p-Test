<?php
namespace ChipTest\Tests;

use PHPUnit\Framework\TestCase;
use ChipTest\InterestAccount;
use Exception;

class InterestAccountTest extends TestCase {

    //region Testing Constants

    // A list of user IDs for testing.
    private const USER_IDS = [
        '332705c0-fadd-4663-ace4-6ea1c3297566', // Used in API_RESPONSE_1 + USER_ACCOUNT_1
        '3567bd11-03c5-44ad-ae5d-5021ac26d210', // Used in API_RESPONSE_2 + USER_ACCOUNT_2
        'e0c1c412-823e-4af8-b256-2d1c22b3b376', // Used in API_RESPONSE_3 + USER_ACCOUNT_3
        '3d676dc7-ed2c-447c-8eb9-8cd8c5e86279', // Used in USER_ACCOUNT_4
        '46437aa0-2386-4c90-b789-8513a47fda27', // Used by Mock API for USER_ACCOUNT_5.
        '5338a798-0ea1-495b-8356-59cc28d9d6ff' // Used to find nonexistant account.
    ];

    private const LOW_INTEREST_RATE = 0.93;
    private const HIGH_INTEREST_RATE = 1.02;
    private const DEFAULT_INTEREST_RATE = 0.5;

    private const USER_ACCOUNT_1 = [
        'id' => '332705c0-fadd-4663-ace4-6ea1c3297566',
        'active' => true,
        'interestRate' => 0.93,
        'despositAmount' => 200,
        'baseAmount' => 200,
        'afterDeposit' => 400,
        // 'afterInterest' = 
    ];

    private const USER_ACCOUNT_2 = [
        'id' => '3567bd11-03c5-44ad-ae5d-5021ac26d210',
        'active' => true,
        'interestRate' => 1.02,
        'depositAmount' => 1000,
        'baseAmount' => 6000,
        'afterDeposit' => 7000,
    ];

    private const USER_ACCOUNT_3 = [
        'id' => 'e0c1c412-823e-4af8-b256-2d1c22b3b376',
        'active' => true,
        'interestRate' => 0.5,
        'depositAmount' => 750,
        'baseAmount' => 0,
        'afterDeposit' => 750,
    ];

    private const USER_ACCOUNT_4 = [
        'id' => '3d676dc7-ed2c-447c-8eb9-8cd8c5e86279',
        'active' => false,
    ];

    /*
     * API response examples: 
     * success (200):
     * 'id' => [
            'type' => 'string',
            'description' => 'User ID',
            'example' => '88224979-406e-4e32-9458-55836e4e1f95'
        ];
     *  'income' => [
            'type' => 'integer',
            'description' => 'Monthly income',
            'nullable' => true,
            'example' => 499999
        ];
     *
     * error/default (anything other than 200):
     * 'code' => [
            'type' => 'integer',
            'description' => 'Error code',
            'example' => 100
        ];
     *  'message' => [
            'type' => 'string',
            'description' => 'Error message',
            'example' => 'Unexpected error'
        ];
     */

    private const API_RESPONSE_1 = [
        'code' => 200,
        'content' => 
            [
                'id' => '332705c0-fadd-4663-ace4-6ea1c3297566',
                'income' => 200
        ]
    ];
    private const API_RESPONSE_2 = [
        'code' => 200,
        'content' => 
            [
                'id' => '3567bd11-03c5-44ad-ae5d-5021ac26d210',
                'income' => 6000
        ]
    ];
    private const API_RESPONSE_3 = [
        'code' => 200,
        'content' => [
                'id' => 'e0c1c412-823e-4af8-b256-2d1c22b3b376',
                'income' => null
        ]
    ];
    private const API_RESPONSE_4 = [
        'code' => 100,
        'content' => [
                'code' => 100,
                'message' => "Example Error."
        ]
    ];
    private const API_RESPONSE_5 = [
        'code' => 404,
        'content' => 
            [
                'code' => 404,
                'message' => "Example Error 2 - Not Found."
        ]
    ];

    //endregion

    private InterestAccount $interestAccount;

    /**
     * Constructor function used by PHPUnit to set up required variables for testing.
     */
    protected function setUp(): void
    {
        $this->interestAccount = new InterestAccount();
    }

    /**
     * Test the new account function, expecting the following:<br>
     * <ul>
     * <li>A new account with a low interest rate.</li>
     * <li>A new account with a high interest rate.</li>
     * <li>A new account with a default interest rate.</li>
     * </ul>
     */
    public function testNewAccount() {
        // Create an account with a low interest rate.
        $this->assertEquals([$this::USER_IDS[0], $this::LOW_INTEREST_RATE], 
            $this->interestAccount->newAccount($this::USER_IDS[0])
        );
        
        // Create an account with a high interest rate.
        $this->assertEquals([$this::USER_IDS[1], $this::HIGH_INTEREST_RATE], 
            $this->interestAccount->newAccount($this::USER_IDS[1])
        );
        
        // Create an account with a default interest rate.
        $this->assertEquals([$this::USER_IDS[2], $this::DEFAULT_INTEREST_RATE], 
            $this->interestAccount->newAccount($this::USER_IDS[2])
        );
    }

    /**
     * Test the getAccount function, expecting the following:<br>
     * <ul>
     * <li></li>
     * </ul>
     */
    public function testGetAccount() {
        $this->assertEquals([$this::USER_IDS[4], $this::LOW_INTEREST_RATE], 
            $this->interestAccount->newAccount($this::USER_IDS[4])
        );
    }

    /**
     * Test the deposit funds function, expecting the following:<br>
     * <ul>
     * <li>Error, trying to deposit without a set account.</li>
     * <li>A new account with a high interest rate.</li>
     * <li>A new account with a default interest rate.</li>
     * <li>False, trying to create an account with an existing userID.</li>
     * <li>False, trying create an account without a userID.</li>
     * </ul>
     */
    // public function testDepositFunds() {


    // }

    /**
     * Test the functions expecting an exception, expecting the following:<br>
     * <ul>
     * <li>Trying to create an account with an existing userID.</li>
     * <li>Trying to create an account without a userID.</li>
     * <li>Trying to deposit without a set account.</li>
     * <li>Calculate interest without a set account.</li>
     * <li>List transactions without a set account.</li>
     * </ul>
     */
    public function testExpectedExceptions() {
        $this->expectException(Exception::class);

        // Attempt to create an account with an already used ID.
        $this->interestAccount->newAccount($this::USER_IDS[4]);

        // Attempt to create an account without an ID.
        $this->interestAccount->newAccount($this::API_RESPONSE_4['content']['id']);

        // Attempt to find a nonexistant account.
        $this->interestAccount->getAccount($this::USER_IDS[5]);

        // Deposit funds without a set account.
        $this->interestAccount->depositFunds(99999);

        // Calculate interet without a set account.
        $this->interestAccount->calculateInterest();

        // List transactions without a set account.
        $this->interestAccount->listTransactions();
    }
}
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
        // Base object parameters.
        'id' => '332705c0-fadd-4663-ace4-6ea1c3297566',
        'income' => 20000,
        'interestRate' => 0.93,
        'balance' => 200,
        // Testing specific parameters onwards.
        'depositAmount' => 200,
        'afterDeposit' => 400,
        'days' => 20, // /3 == 6 instances of interest.
        'afterInterest' => 206,
        'afterInterestLeapYear' => 206,
        'transactions' => [
            ['Expense', 20000],
            ['Deposit', 20000],
            ['Interest', 1],
            ['Income', 20000],
            ['Expense', 20000]
        ],
        'transactionsAfterInterest' => [
            ['Expense', 20000],
            ['Deposit', 20000],
            ['Interest', 1],
            ['Income', 20000],
            ['Expense', 20000],
            ['Interest', 1],
            ['Interest', 1],
            ['Interest', 1],
            ['Interest', 1],
            ['Interest', 1],
            ['Interest', 1]
        ],
        'transactionsAfterDeposit' => [
            ['Expense', 20000],
            ['Deposit', 20000],
            ['Interest', 1],
            ['Income', 20000],
            ['Expense', 20000],
            ['Deposit', 200]
        ]
    ];

    private const USER_ACCOUNT_2 = [
        // Base object parameters.
        'id' => '3567bd11-03c5-44ad-ae5d-5021ac26d210',
        'income' => 600000,
        'interestRate' => 1.02,
        'balance' => 600000,
        // Testing specific parameters onwards.
        'depositAmount' => 100000,
        'afterDeposit' => 700000,
        'days' => 8, // /3 == 2 instances of interest.
        'afterInterest' => 610102,
        'afterInterestLeapYear' => 610074,
        'transactions' => [
            ['Expense', 2000],
            ['Deposit', 100000],
            ['Expense', 2000],
            ['Interest', 10],
            ['Income', 600000]
        ],
        'transactionsAfterInterest' => [
            ['Expense', 2000],
            ['Deposit', 100000],
            ['Expense', 2000],
            ['Interest', 10],
            ['Income', 600000],
            ['Interest', 5030],
            ['Interest', 5072]
        ],
        'transactionsAfterDeposit' => [
            ['Expense', 2000],
            ['Deposit', 100000],
            ['Expense', 2000],
            ['Interest', 10],
            ['Income', 600000],
            ['Deposit', 100000]
        ]
    ];

    private const USER_ACCOUNT_3 = [
        // Base object parameters.
        'id' => 'e0c1c412-823e-4af8-b256-2d1c22b3b376',
        'income' => null,
        'interestRate' => 0.5,
        'balance' => 1,
        // Testing specific parameters onwards.
        'depositAmount' => 750,
        'afterDeposit' => 751,
        'days' => 32, // /3 == 10 instances of interest.
        'afterInterest' => 1,
        'afterInterestLeapYear' => 1,
        'transactions' => [
            ['Expense', 2000],
            ['Deposit', 100000],
            ['Expense', 2000]
        ],
        'transactionsAfterInterest' => [
            ['Expense', 2000],
            ['Deposit', 100000],
            ['Expense', 2000]
        ],
        'transactionsAfterDeposit' => [
            ['Expense', 2000],
            ['Deposit', 100000],
            ['Expense', 2000],
            ['Deposit', 750]
        ]
    ];

    // Set in the faux endpoint.
    private const USER_ACCOUNT_4 = [
        'id' => '3d676dc7-ed2c-447c-8eb9-8cd8c5e86279',
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

    //region Tests

    /**
     * Test the new account function, expecting the following:
     * 
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
     * Test setting an account and getting the details of.
     */
    public function testSetAndGetAccount() {
        $this->interestAccount->setAccount($this::USER_ACCOUNT_1);
        $this->assertEquals($this->interestAccount->getAccount(), [
            'id' => $this::USER_ACCOUNT_1['id'],
            'balance' => $this::USER_ACCOUNT_1['balance'],
            'interestRate' => $this::USER_ACCOUNT_1['interestRate'],
            'income' => $this::USER_ACCOUNT_1['income']
        ]);
    }

    /**
     * Test the deposit funds function, expecting the following:
     * 
     * <ul>
     * <li>A user has deposited funds, and their value has increased. * 3</li>
     * </ul>
     */
    public function testDepositFunds() {
        $this->interestAccount->setAccount($this::USER_ACCOUNT_1);
        $test1After = $this->interestAccount->depositFunds($this::USER_ACCOUNT_1['depositAmount']);
        $this->assertEquals($this::USER_ACCOUNT_1['afterDeposit'], $test1After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_2);
        $test2After = $this->interestAccount->depositFunds($this::USER_ACCOUNT_2['depositAmount']);
        $this->assertEquals($this::USER_ACCOUNT_2['afterDeposit'], $test2After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_3);
        $test3After = $this->interestAccount->depositFunds($this::USER_ACCOUNT_3['depositAmount']);
        $this->assertEquals($this::USER_ACCOUNT_3['afterDeposit'], $test3After['balance']);
    }

    /**
     * Test the calculate interest function, expecting the following:
     * 
     * <ul>
     * <li>User account 1, twenty days.</li>
     * <li>User account 1, twenty days, leap year.</li>
     * <li>User account 2, eight days.</li>
     * <li>User account 2, eight days, leap year.</li>
     * <li>User account 3, thirty two days.</li>
     * <li>User accoutn 3, thirty two days, leap year.</li>
     * </ul>
     */
    public function testCalculateInterest() {
        $this->interestAccount->setAccount($this::USER_ACCOUNT_1);
        $test1After = $this->interestAccount->calculateInterest($this::USER_ACCOUNT_1['days']);
        $this->assertEquals($this::USER_ACCOUNT_1['afterInterest'], $test1After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_1);
        $test1After = $this->interestAccount->calculateInterest($this::USER_ACCOUNT_1['days'], true);
        $this->assertEquals($this::USER_ACCOUNT_1['afterInterestLeapYear'], $test1After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_2);
        $test2After = $this->interestAccount->calculateInterest($this::USER_ACCOUNT_2['days']);
        $this->assertEquals($this::USER_ACCOUNT_2['afterInterest'], $test2After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_2);
        $test2After = $this->interestAccount->calculateInterest($this::USER_ACCOUNT_2['days'], true);
        $this->assertEquals($this::USER_ACCOUNT_2['afterInterestLeapYear'], $test2After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_3);
        $test3After = $this->interestAccount->calculateInterest($this::USER_ACCOUNT_3['days']);
        $this->assertEquals($this::USER_ACCOUNT_3['afterInterest'], $test3After['balance']);

        $this->interestAccount->setAccount($this::USER_ACCOUNT_3);
        $test3After = $this->interestAccount->calculateInterest($this::USER_ACCOUNT_3['days'], true);
        $this->assertEquals($this::USER_ACCOUNT_3['afterInterestLeapYear'], $test3After['balance']);
    }

    /**
     * Test the get and logging of transactions, after performing the following:
     * 
     * <ul>
     * <li>User account 1, interest.</li>
     * <li>User account 2, interest.</li>
     * <li>User account 3, interest.</li>
     * <li>User account 1, deposit.</li>
     * <li>User account 2, deposit.</li>
     * <li>User accoutn 3, deposit.</li>
     * </ul>
     *
     * @return void
     */
    public function testGetTransactions() {
        // Interest transactions.
        $this->interestAccount->setAccount($this::USER_ACCOUNT_1);
        $this->interestAccount->calculateInterest($this::USER_ACCOUNT_1['days']);
        $this->assertEquals($this::USER_ACCOUNT_1['transactionsAfterInterest'], 
            $this->interestAccount->getTransactions());

        $this->interestAccount->setAccount($this::USER_ACCOUNT_2);
        $this->interestAccount->calculateInterest($this::USER_ACCOUNT_2['days']);
        $this->assertEquals($this::USER_ACCOUNT_2['transactionsAfterInterest'], 
            $this->interestAccount->getTransactions());

        $this->interestAccount->setAccount($this::USER_ACCOUNT_3);
        $this->interestAccount->calculateInterest($this::USER_ACCOUNT_3['days']);
        $this->assertEquals($this::USER_ACCOUNT_3['transactionsAfterInterest'], 
            $this->interestAccount->getTransactions());

        // Deposit transactions.
        $this->interestAccount->setAccount($this::USER_ACCOUNT_1);
        $this->interestAccount->depositFunds($this::USER_ACCOUNT_1['depositAmount']);
        $this->assertEquals($this::USER_ACCOUNT_1['transactionsAfterDeposit'], 
            $this->interestAccount->getTransactions());

        $this->interestAccount->setAccount($this::USER_ACCOUNT_2);
        $this->interestAccount->depositFunds($this::USER_ACCOUNT_2['depositAmount']);
        $this->assertEquals($this::USER_ACCOUNT_2['transactionsAfterDeposit'], 
            $this->interestAccount->getTransactions());

        $this->interestAccount->setAccount($this::USER_ACCOUNT_3);
        $this->interestAccount->depositFunds($this::USER_ACCOUNT_3['depositAmount']);
        $this->assertEquals($this::USER_ACCOUNT_3['transactionsAfterDeposit'], 
            $this->interestAccount->getTransactions());
    }

    /**
     * Test the functions expecting an exception, for the following:
     * 
     * <ul>
     * <li>Trying to create an account with an existing userID.</li>
     * <li>Trying to create an account with an invalid userID.</li>
     * <li>Trying to create an account without a userID.</li>
     * <li>Trying to set account details with missing data.</li>
     * <li>Trying to set account details with an invalid userID.</li>
     * <li>Trying to get details without a set account.</li>
     * <li>Trying to deposit without a set account.</li>
     * <li>Calculate interest without a set account.</li>
     * <li>List transactions without a set account.</li>
     * </ul>
     */
    public function testExpectedExceptions() {
        $this->expectException(Exception::class);

        // Attempt to create an account with an already used ID.
        $this->interestAccount->newAccount($this::USER_IDS[4]);

        // User ID is in an invalid format.
        $this->interestAccount->newAccount('test');

        // Attempt to create an account without an ID.
        $this->interestAccount->newAccount("");

        // Attempt to set an account with one of each variable missing.
        $this->interestAccount->setAccount([null, 9999, 9999, 1]);
        // Income is nullable.
        $this->interestAccount->setAccount([$this::USER_IDS[4], 9999, null, 1]);
        $this->interestAccount->setAccount([$this::USER_IDS[4], 9999, 9999, null]);
        
        // User ID is not in a proper format.
        $this->interestAccount->setAccount(['test', 9999, 9999, 1]);

        $emptyInterestAccount = new InterestAccount;
        $emptyInterestAccount->getAccount();

        // Deposit funds without a set account.
        $emptyInterestAccount->depositFunds(99999);

        // Calculate interet without a set account.
        $emptyInterestAccount->calculateInterest(100);

        // List transactions without a set account.
        $emptyInterestAccount->getTransactions();
    }

    //endregion
}
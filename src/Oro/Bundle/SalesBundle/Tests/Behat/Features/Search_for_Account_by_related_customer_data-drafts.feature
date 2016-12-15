@not-automated
@draft

  # functionality - to be tested for:
  # CRM - Magento customers
  # commerce - magento and commerce customers

  #Also to be tested in search results (manually):
  #General set of input which can be tested is:
  # A-Z
  # a-z
  # 0-9
  # special characters
  # Blank spaces
  # special set - 2 blank spaces – should be trimmed and error message should be displayed
  # Blank spaces followed with special characters or numbers
  # Special set like a* should give the results for all characters starting with a
  # any sql query like “Select * from hello;” without quotes and with quotes
  # Try pressing “Enter” key instead of clicking “Search” button
  # Proper messages should be displayed when there are no results
  # Searched keyword should be highlighted in the search results
  # Does the search box presents auto suggestions when the query is being typed?

   # At the moment the way the user can configure number of search results, is temporarily located at:
   # System Configuration - CRM - Opportunity - Autocomplete settings
   # This will be changed - when? How? Update tc later when it will be done

Feature: In order to quickly find necessary account even if I don't remember it's name
    As an Administrator
    I want to be able to search for accounts by customers' data

   Background:
    Given the owner named John Doe
    And account named Jennifer Lawrence
    And two contacts that belong to account "Jennifer Lawrence"
      | Name          | Phone       | Email                   |
      | Chris Pratt   | 0501463398  | whatever@passengers.com |
      | Jeremy Renner | 0501487795  | screenplay@arrival.com  |
    And Magento Customer:
      | Name          | Phone       | Email                   |
      | Josh Gordon   | 0672659873  | officeparty@bateman.com |
    And Commerce Customer
      | Name          | Phone       | Email                   |
      | Justin Malen  | 0678542697  | review@metascore.com    |
    And 25 contacts with the following data:
      | Contact             | Phone       |
      | Drew Latham         | 0671234568  |
      | Tom Valco           | 0671598763  |
      | Ben Affleck         | 0671598652  |
      | James Gandolfini    | 0672659748  |
      | Christina Applegate | 0675987662  |
      | Catherine O'Hara    | 0673598735  |
      | Josh Zuckerman      | 0672659756  |
      | Bill Macy           | 0674523698  |
      | Jennifer Morrison   | 0675855698  |
      | Udo Kier            | 0673369561  |
      | David Selby         | 0675589998  |
      | Stephanie Faracy    | 0675558883  |
      | Stephen Root        | 0674448563  |
      | Sy Richardson       | 0673336974  |
      | Tangie Ambrose      | 0674477752  |
      | Peter Jason         | 0679996325  |
      | Phill Lewis         | 0673336955  |
      | Kate Hendrickson    | 0676662598  |
      | Bridgette Ho        | 0678845888  |
      | Sean Marquette      | 0675548321  |
      | Caitlin Fein        | 0673328547  |
      | Amanda Fein         | 0674411158  |
      | Kent Osborne        | 0671115989  |
      | Bill Saito          | 0672259637  |
      | Mike Bell           | 0671115872  |

  Scenario Outline: Searching for keyword by relevant account name
    Given I log in as Administrator
    And I go to Sales/ Opportunities
    And I press "Create Opportunity" button
    And "Create Opportunity" form is displayed
    And I start typing <keyword> in "Account" field
    Then I should see "Jennifer Lawrence" account in search result
    And I should see all relevant accounts that contain <keyword>
    And <keyword> in search results should be underlined
    And "Jennifer Lawrence" account should be displayed as "Jennifer Lawrence" (Account)
    Examples:
        | keyword   |
        | Je        |
        | je        |
        | JE        |
        | jE        |
        | Jenn      |
        | JENN      |
        | jENN      |
        | Jennyfer  |
        | JENNYFER  |
        | JennYFER  |
        | La        |
        | Law       |
        | LAWRENCE  |
        | lawrence  |
        | Lawrence  |

    Scenario Outline: Searching for keyword by relevant contact name
      Given I am on "create Opportunity" page
      And I start typing <keyword> in "Account" field
      Then I should see "Chris Pratt" contact in search result
      And I should see all relevant contacts that contain <keyword>
      And <keyword> in search results should be underlined
      And grouping for search results should be the following: Account - relevant contact
      And "Chris Pratt" contact should be displayed as "Chris Pratt" (Contact)
    Examples:
        | keyword |
        | Ch      |
        | CH      |
        | cH      |
        | ch      |
        | Chris   |
        | CHRIS   |
        | chRIS   |
        | Pratt   |
        | pratt   |
        | PRATT   |
        | prATT   |
        | pr      |
        | PR      |

    Scenario Outline: Searching for keyword by relevant contact phone
      Given I am on "Create Opportunity" page
      And I start typing <keyword> in "Account" field
      Then I should see "Chris Pratt" contact in search result
      And I should see all relevant contacts that contain <keyword>
      And <keyword> in search results should be underlined
      And grouping for search results should be the following: Account - relevant contact
      And "Chris Pratt" contact should be displayed as "Chris Pratt" (Contact)
    Examples:
      | keyword     |
      | 05          |
      | 050         |
      | 0501        |
      | 05014       |
      | 050148      |
      | 0501487     |
      | 05014877    |
      | 050148779   |
      | 0501487795  |

    Scenario Outline: Searching for keyword by relevant contact email
      Given I am on "Create Opportunity" page
      And I start typing <keyword> in "Account" field
      Then I should see "Chris Pratt" contact in search result
      And I should see all relevant contacts that contain <keyword>
      And <keyword> in search results should be underlined
      And grouping for search results should be the following: Account - relevant contact
      And "Chris Pratt" contact should be displayed as "Chris Pratt (Contact)"
    Examples:
      | keyword                 |
      | sc                      |
      | scr                     |
      | scre                    |
      | scree                   |
      | screen                  |
      | screenp                 |
      | screenpl                |
      | screenpla               |
      | screenplay              |
      | screenplay@             |
      | screenplay@a            |
      | screenplay@arrival.com  |
      | @arrival.com            |
      | arrival.com             |
      | .com                    |
      | SCREENPLAY@ARRIVAL.COM  |

  Scenario Outline: Searching for keyword by relevant Magento customer's name
    Given I am on "Create Opportunity" page
    And I start typing <keyword> in "Account" field
    Then I should see "Josh Gordon" Magento customer in search result
    And I should see all relevant customers that contain <keyword>
    And <keyword> in search results should be underlined
    And grouping for search results should be the following: Account - relevant magento customer
    And "Josh Gordon" customer should be displayed as "Josh Gordon" (Magento Customer)
  Examples:
      | keyword |
      | Jo      |
      | JO      |
      | jO      |
      | jo      |
      | Josh    |
      | JOSH    |
      | joSH    |
      | Gordon  |
      | gordon  |
      | GORDON  |
      | goRDON  |
      | GO      |
      | go      |

  Scenario Outline: Searching for keyword by relevant Magento customer's email
    Given I am on "Create Opportunity" page
    And I start typing <keyword> in "Account" field
    Then I should see "Josh Gordon" Magento customer in search result
    And I should see all relevant contacts that contain <keyword>
    And <keyword> in search results should be underlined
    And grouping for search results should be the following: Account - relevant magento customer
    And "Josh Gordon" Magento customer should be displayed as "Josh Gordon" (Magento customer)
  Examples:
      | keyword                 |
      | of                      |
      | off                     |
      | offi                    |
      | office                  |
      | officep                 |
      | officepa                |
      | officepar               |
      | officepart              |
      | officeparty             |
      | officeparty@            |
      | officeparty@b           |
      | officeparty@bateman.com |
      | @bateman.com            |
      | bateman.com             |
      | .com                    |
      | BATEMAN.COM             |

  Scenario Outline: Searching for keyword by relevant Magento customer phone

    Given I am on "Create Opportunity" page
    And I start typing <keyword> in "Account" field
    Then I should see "Josh Gordon" contact in search result
    And I should see all relevant contacts that contain <keyword>
    And <keyword> in search results should be underlined
    And grouping for search results should be the following: Account - relevant contact
    And "Josh Gordon" contact should be displayed as "Josh Gordon" (Contact)
  Examples:
      | keyword     |
      | 06          |
      | 067         |
      | 0672        |
      | 06726       |
      | 067265      |
      | 0672659     |
      | 06726598    |
      | 067265987   |
      | 0672659873  |

    Scenario Outline: Searching for keyword by relevant Commerce customer's name

      Given I am on "Create Opportunity" page
      And I start typing <keyword> in "Account" field
      Then I should see "Justin Malen" Commerce customer in search result
      And I should see all relevant customers that contain <keyword>
      And <keyword> in search results should be underlined
      And grouping for search results should be the following: Account - relevant commerce customer
      And "Justin Malen" customer should be displayed as "Justin Malen" (Commerce Customer)
    Examples:
        | keyword |
        | Ju      |
        | JU      |
        | jU      |
        | ju      |
        | Justin  |
        | JUSTIN  |
        | juSTIN  |
        | Malen   |
        | malen   |
        | MALEN   |
        | maLEN   |
        | MA      |
        | ma      |

    Scenario Outline: Searching for keyword by relevant Commerce customer's email
      Given I am on "Create Opportunity" page
      And I start typing <keyword> in "Account" field
      Then I should see "Justin Malen" Commerce customer in search result
      And I should see all relevant Commerce customers that contain <keyword>
      And <keyword> in search results should be underlined
      And grouping for search results should be the following: Account - relevant Commerce customer
      And "Justin Malen" Commerce customer should be displayed as "Justin Malen" (Commerce customer)
    Examples:
        | keyword               |
        | re                    |
        | rev                   |
        | revi                  |
        | revie                 |
        | review                |
        | review@               |
        | review@me             |
        | review@metascore.com  |
        | @metascore.com        |
        | metascore.com         |
        | .com                  |
        | METASCORE.COM         |



    Scenario: Searching for keyword by relevant Commerce customer phone

      Given I am on "Create Opportunity" page
      And I start typing <keyword> in "Account" field
      Then I should see "Justin Malen" Commerce customer in search result
      And I should see all relevant Commerce customers that contain <keyword>
      And <keyword> in search results should be underlined
      And grouping for search results should be the following: Account - relevant Commerce customer
      And "Justin Malen" Commerce customer should be displayed as "Justin Malen" (Commerce customer)

    Examples:
        | keyword     |
        | 06          |
        | 067         |
        | 0678        |
        | 06785       |
        | 067854      |
        | 0678542     |
        | 06785426    |
        | 067854269   |
        | 0678542697  |

  Scenario: Checking that if there are more than 20 results, the user can scroll to reveal additional results
    Given I am on "Create Opportunity" page
    And I start typing "067" in "Account" field
    Then I should see 20 relevant contacts in search result from the following table:

        | Contact             | Phone       |
        | Drew Latham         | 0671234568  |
        | Tom Valco           | 0671598763  |
        | Ben Affleck         | 0671598652  |
        | James Gandolfini    | 0672659748  |
        | Christina Applegate | 0675987662  |
        | Catherine O'Hara    | 0673598735  |
        | Josh Zuckerman      | 0672659756  |
        | Bill Macy           | 0674523698  |
        | Jennifer Morrison   | 0675855698  |
        | Udo Kier            | 0673369561  |
        | David Selby         | 0675589998  |
        | Stephanie Faracy    | 0675558883  |
        | Stephen Root        | 0674448563  |
        | Sy Richardson       | 0673336974  |
        | Tangie Ambrose      | 0674477752  |
        | Peter Jason         | 0679996325  |
        | Phill Lewis         | 0673336955  |
        | Kate Hendrickson    | 0676662598  |
        | Bridgette Ho        | 0678845888  |
        | Sean Marquette      | 0675548321  |
        | Caitlin Fein        | 0673328547  |
        | Amanda Fein         | 0674411158  |
        | Kent Osborne        | 0671115989  |
        | Bill Saito          | 0672259637  |
        | Mike Bell           | 0671115872  |
    And the user should be able to scroll to reveal additional 5 results

  Scenario: Adding new contact
    Given I am on "Create Opportunity" page
    And I input Sonya Eddy into the Account field
    Then I should see "Sonya Eddy" (Add new) suggestion
    And when I select "Sonya Eddy"
    Then I should see "Sonya Eddy" in the "Account" field

  Scenario: Relating opportunity to the account
    Given I am on "Create Opportunity" page
    And I start typing "officeparty@bateman.com" into the Account field
    Then "Josh Gordon" Magento Customer appears in search results
    And when I select "Josh Gordon" Magento Customer from the search results
    Then I should see "Josh Gordon" Magento Customer in the "Account" field
    And when I fill out all other details for the Opportunity as follows:
        | Owner     | Opportunity Name  | Channel       | Status  |
        | John Doe  | New Opportunity   | Sales Channel | Open    |
    And I press "Save And Close" button
    Then I should see the following on the "New Opportunity" view page:
        | Owner     | Opportunity Name  | Channel       | Status  | Account     |
        | John Doe  | New Opportunity   | Sales Channel | Open    | Josh Gordon |

  Scenario: Search results should not be shown for entities names
    Given I am on "Create Opportunity" page
    And I start typing <Entity name> into the Account field
    Then nothing appears in search results
  Examples:
        | Entity name       |
        | Magento Customer  |
        | Business Customer |
        | Account           |

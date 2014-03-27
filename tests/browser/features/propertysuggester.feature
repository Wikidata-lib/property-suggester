@wikidata.beta.wmflabs.org
Feature: Checking propertysuggester
	Background:
	    Given I am on an item page
	      And The copyright warning has been dismissed
	      And Anonymous edit warnings are disabled

	@ui_only @repo_login
	Scenario: suggestions on input in new object
	When I click the statement add button
		And I enter b in the property input field
		And Entity selector input element should be there
		And Entity selector list should be there


	Scenario: suggestions on input (with pre-added statement)
	When I click the statement add button
		And I enter date of birth in the property input field
		And I enter 23.01.1990 as string statement value
		And Statement save button should be there
		And I click the statement save button
		
		And Statement add button should be there

		And I click the right statement add button
		And I enter N in the property input field
		And Entity selector input element should be there
		And Entity selector list should be there

	Scenario: suggestions are displayed on focus
		When I click the right statement add button
		And Entity selector list should be there
		#And I enter Hanna as string statement value
		#And Statement save button should be there
		#And I click the statement save button
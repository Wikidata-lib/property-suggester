@wikidata.beta.wmflabs.org
Feature: Checking propertysuggester
	Background:
	    Given I am on an item page
	      And The copyright warning has been dismissed
	      And Anonymous edit warnings are disabled

	@ui_only @repo_login
	Scenario: Add and save a property
	When I click the statement add button
		And I enter date of birth in the property input field
		#And I select the property 569
		And I enter 23.01.1990 as string statement value
		And Statement save button should be there
		And I click the statement save button
		#And Entity selector input element should be there
		#And Statement value input element should be there

		And Statement add button should be there
		And I click the statement add button
		And I enter named after in the property input field
		And I enter Hanna as string statement value
		And Statement save button should be there
		And I click the statement save button
		And Entity selector input element should be there
		And Statement value input element should be there
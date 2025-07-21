@movement
Feature: Movement
  In order to move around the world
  As Player
  I want to move my character between locations

  Background:
    Given there is the location "A"
    And there is the location "B" north from location "A"
    And there is the location "C" east from location "A"
    And there is the location "D" south from location "A"
    And there is the location "E" west from location "A"
    And there is the location "F" up from location "A"
    And there is the location "G" down from location "A"
    And my character is in the location "A"

  @domain @socket
  Scenario: Moving north
    When I move to north
    Then I should see that I am in the location "B"
    And I should see that there is a location "A" south from here

  @domain @socket
  Scenario: Moving east
    When I move to east
    Then I should see that I am in the location "C"
    And I should see that there is a location "A" west from here

  @domain @socket
  Scenario: Moving south
    When I move to south
    Then I should see that I am in the location "D"
    And I should see that there is a location "A" north from here

  @domain @socket
  Scenario: Moving west
    When I move to west
    Then I should see that I am in the location "E"
    And I should see that there is a location "A" east from here

  @domain @socket
  Scenario: Moving up
    When I move to up
    Then I should see that I am in the location "F"
    And I should see that there is a location "A" down from here

  @domain @socket
  Scenario: Moving down
    When I move to down
    Then I should see that I am in the location "G"
    And I should see that there is a location "A" up from here

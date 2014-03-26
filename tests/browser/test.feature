require "selenium/client"
require "test/unit"

class NewTest < Test::Unit::TestCase
  def setup
    @verification_errors = []
    if $selenium
      @selenium = $selenium
    else
      @selenium =  Selenium::Client::Driver.new("localhost", 80, "*firefox", "http://www.google.com/", 60);
      @selenium.start
    end
    @selenium.set_context("test_new")
  end

  def teardown
    @selenium.stop unless $selenium
    assert_equal [], @verification_errors
  end

  def test_new
    @selenium.open "/"
    @selenium.type "q", "selenium rc"
    @selenium.click "btnG"
    @selenium.wait_for_page_to_load "30000"
    assert @selenium.is_text_present("Results * for selenium rc")
  end
end
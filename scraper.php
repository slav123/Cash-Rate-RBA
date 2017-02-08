<?php
// This is a template for a PHP scraper on morph.io (https://morph.io)
// including some code snippets below that you should find helpful

require 'scraperwiki.php';
require 'scraperwiki/simple_html_dom.php';


$url = '';

// Read in a page
$html = scraperwiki::scrape('http://www.rba.gov.au/statistics/cash-rate/');

// Find something on the page using css selectors
$dom = new simple_html_dom();
$dom->load($html);
$ret = $dom->find('#datatable tr');
foreach ($ret as $row)
{
	$effective_date = $row->find('th', 0)->plaintext;
	$change         = $row->find('td', 0)->plaintext;
	$cash_rate      = $row->find('td', 1)->plaintext;

	if ($effective_date)
	{
		scraperwiki::save_sqlite(array('effective_date'), array(
			'effective_date' => date("Y-m-d", strtotime($effective_date)),
			'change'         => $change,
			'cash_rate'      => $cash_rate
		));
	}
}


// // An arbitrary query against the database
// scraperwiki::select("* from data where 'name'='peter'")

// You don't have to do things with the ScraperWiki library.
// You can use whatever libraries you want: https://morph.io/documentation/php
// All that matters is that your final data is written to an SQLite database
// called "data.sqlite" in the current working directory which has at least a table
// called "data".
?>

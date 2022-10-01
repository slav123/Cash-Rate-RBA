<?php
// This is a template for a PHP scraper on morph.io (https://morph.io)
// including some code snippets below that you should find helpful

require 'scraperwiki.php';
require 'simple_html_dom.php';


$url = 'https://www.rba.gov.au/statistics/cash-rate/';

// Read in a page
$html = scraperwiki::scrape($url);

//$html = file_get_contents($url);

$dom = new simple_html_dom();
$dom->load($html);
$ret = $dom->find('#datatable tr');


//scraperwiki::delete("* from data where 'name'='peter'")

foreach ($ret as $row)
{
	if ($obj_effective_date = $row->find('th', 0))
	{
		$effective_date = $obj_effective_date->plaintext;
	}

	if ($obj_change = $row->find('td', 0))
	{
		$change = $obj_change->plaintext;
	}

	if ($obj_cash_rate = $row->find('td', 1))
	{
		$cash_rate = $obj_cash_rate->plaintext;
	}

	if (isset($effective_date) && isset($change))
	{
		scraperwiki::save_sqlite(array('effective_date'), array(
			'effective_date' => date('Y-m-d', strtotime($effective_date)),
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

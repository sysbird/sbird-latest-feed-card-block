<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'sysbird/rss-card',
		'version' => '1.0.0',
		'title' => 'sBird Latest Feed Card Block',
		'category' => 'embed',
		'description' => 'Display the latest entry from an external RSS feed.',
		'attributes' => array(
			'feedUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'hasBorder' => array(
				'type' => 'boolean',
				'default' => true
			),
			'layout' => array(
				'type' => 'string',
				'default' => 'horizontal'
			)
		),
		'render' => 'file:./render.php',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'sbird-latest-feed-card-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./editor.css'
	)
);

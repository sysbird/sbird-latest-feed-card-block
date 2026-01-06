<?php
// This file is generated. Do not modify it manually.
return array(
	'rss-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'sysbird/rss-card',
		'version' => '0.1.0',
		'title' => 'RSS Card',
		'category' => 'embed',
		'icon' => 'smiley',
		'description' => 'Display the latest entry from an external RSS feed.',
		'attributes' => array(
			'feedUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'hasBorder' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'render' => 'file:./render.php',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'rss-card',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);

<?php
class Comment {
	
	var $comment_id;
	var $ref;
	var $ref_id;
	var $author_uid;
	var $author_name;
	var $comment_text;
	
	function __construct($row) {
		$this->comment_id = $row['comment_id'];
		$this->ref = $row['ref'];
		$this->ref_id = $row['ref_id'];
		$this->author_uid = $row['author_uid'];
		$this->author_name = $row['author_name'];
		$this->comment_text = $row['comment_text'];
	}
	
}
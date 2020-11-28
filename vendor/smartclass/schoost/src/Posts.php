<?php

/*
 * This file is part of Schoost.
 *
 * (c) SmartClass, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Schoost;

use \Twig\Loader;

class Posts {
    
    private $posts = array();
    private $postId = 0;
    private $commentId = 0;

    /* function */
	function setPostId($Id)
	{
		$this->postId = $Id;
	}

    /* function */
	function getPosts($page = 1, $limit = 6)
	{
        global $dbi, $ySubeKodu, $aid, $currentlang;
        
		$dbi->pageLimit = $limit;
		$dbi->orderBy("createdAt", "desc");
		$dbi->where("schoolId", $ySubeKodu);
		$posts = $dbi->paginate(_SOCIAL_POSTS_, $page);
		
		if(!empty($posts))
		{
			//handle posts for template
			foreach($posts as $k => $p) {
				
				//user picture
				$posts[$k]["userPicture"] = UserPicture($p["userId"]);
				if(empty($posts[$k]["userPicture"])) $posts[$k]["userPicture"] = "images/" . $currentlang . "/no_image.jpg";
				
				//user name
				$posts[$k]["userName"] = YoneticiAdi($p["userId"]);
				
				//post dated
				$posts[$k]["postedAgo"] = timeAgoCalculate($p["createdAt"]);
				
				//add classes to the post
			    $posts[$k]["batches"] = empty($p["classes"]) ? array(_ALL) : array_map("SinifAdi", explode(",", $p["classes"]));
			    
			    //get the number of likes for the post
			    $posts[$k]["likes"] = $dbi->where("postId", $p["Id"])->getValue(_SOCIAL_LIKES_, "COUNT(Id)");
			    
			    //get user info
			    /* there is something wrong here so comment it out */
			    /*
			    $nameSurnames = array();
			    foreach ($p["likes"] as $like) {
			    	$nameSurnames[] = YoneticiAdi($like["userId"]);
			    }
			    $posts[$k]["users"] = implode(',', $nameSurnames);
			    */
			    
			    //get comments in social_comments table with postId condition
			    $comments = $dbi->where("postId", $p["Id"])->get(_SOCIAL_COMMENTS_, null, "Id, comment, userId, postId, createdAt");
			    foreach($comments as $t => $c) {

					//user picture
					$comments[$t]["userPicture"] = UserPicture($c["userId"]);
					
					//user name
					$comments[$t]["userName"] = YoneticiAdi($c["userId"]);
					
					//post dated
					$comments[$t]["postedAgo"] = timeAgoCalculate($c["createdAt"]);
			    }
				
				$posts[$k]["comments"] = $comments;
				$posts[$k]["nofComments"] = sizeof($comments);
				$posts[$k]["nofCommentsString"] = sizeof($comments) < 2 ? sprintf(_D_COMMENT, $posts[$k]["nofComments"]) : sprintf(_D_COMMENTS, $posts[$k]["nofComments"]);

			    //get photos of post
			    $posts[$k]["photos"] = $dbi->where("postId", $p["Id"])->get(_SOCIAL_PHOTOS_);
				
				//get documents of post
				$posts[$k]["documents"] = $dbi->where("postId", $p["Id"])->get(_SOCIAL_DOCUMENTS_);
				
			    //get my like counts
			    $posts[$k]["myLikeCount"] = $dbi->where("postId", $p["Id"])->where("userId", $aid)->getValue(_SOCIAL_LIKES_, "COUNT(Id)");
			    
			    //if I liked the post, this condition works
			    $posts[$k]["iliked"] = $posts[$k]["myLikeCount"] > 0 ? "1" : "0";
			
				//file url
			    $posts[$k]["noImageUrl"] = "images/" . $currentlang;
			}
		
			$this->posts = $posts;
		}
		
		return $this->posts;
	}

    /* function */
	function deletePost()
	{
        global $dbi;
        
		// delete comment with the id
		$dbi->where("Id", $this->postId);
		$result = $dbi->delete(_SOCIAL_POSTS_);
        
		return $result;
	}
	
	/* 
	 * Comments
	 */
    
    /* function */
	function setCommentId($Id)
	{
		$this->commentId = $Id;
	}

    /* function */
	function getComment()
	{
        global $dbi;
        
        //get comment
		$dbi->where("Id", $this->commentId);
		$row = $dbi->getOne(_SOCIAL_COMMENTS_, "Id, comment, userId, postId, createdAt");
		
		//user picture
		$row["userPicture"] = UserPicture($row["userId"]);
		
		//user name
		$row["userName"] = YoneticiAdi($row["userId"]);
		
		//post dated
		$row["postedAgo"] = timeAgoCalculate($row["createdAt"]);
		
		return $row;
	}
    
    /* function */
	function saveComment($data)
	{
        global $dbi;
        
		// insert comment in social_comment table
		$result = $dbi->insert(_SOCIAL_COMMENTS_, $data);
        
		return $result;
	}

    /* function */
	function getComments()
	{
        global $dbi;
        
        //get comment
		$dbi->where("postId", $this->postId);
		$rows = $dbi->get(_SOCIAL_COMMENTS_, null, "Id, comment, userId, postId, createdAt");

	    foreach($rows as $t => $c) {

			//user picture
			$rows[$t]["userPicture"] = UserPicture($c["userId"]);
			
			//user name
			$rows[$t]["userName"] = YoneticiAdi($c["userId"]);
			
			//post dated
			$rows[$t]["postedAgo"] = timeAgoCalculate($c["createdAt"]);
	    }
        
		return $rows;
	}

    /* function */
	function nofComments()
	{
        global $dbi;
        
		$dbi->where("postId", $this->postId);
		$nofComments = $dbi->getValue(_SOCIAL_COMMENTS_, "COUNT(Id)");
        
		return $nofComments;
	}

    /* function */
	function deleteComment()
	{
        global $dbi;
        
		// delete comment with the id
		$dbi->where("Id", $this->commentId);
		$result = $dbi->delete(_SOCIAL_COMMENTS_);
        
		return $result;
	}

}

<?php

class Board extends CI_Controller {


    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    }

    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions

    		if (!isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again

	    	return call_user_func_array(array($this, $method), $params);
    }


    function index() {
		$user = $_SESSION['user'];

	    	$this->load->model('user_model');
	    	$this->load->model('invite_model');
	    	$this->load->model('match_model');

	    	$user = $this->user_model->get($user->login);

	    	$invite = $this->invite_model->get($user->invite_id);

	    	if ($user->user_status_id == User::WAITING) {
	    		$invite = $this->invite_model->get($user->invite_id);
	    		$otherUser = $this->user_model->getFromId($invite->user2_id);
	    	}
	    	else if ($user->user_status_id == User::PLAYING) {
	    		$match = $this->match_model->get($user->match_id);
	    		if ($match->user1_id == $user->id)
	    			$otherUser = $this->user_model->getFromId($match->user2_id);
	    		else
	    			$otherUser = $this->user_model->getFromId($match->user1_id);
	    	}

	    	$data['user']=$user;
	    	$data['otherUser']=$otherUser;

	    	switch($user->user_status_id) {
	    		case User::PLAYING:
	    			$data['status'] = 'playing';
	    			break;
	    		case User::WAITING:
	    			$data['status'] = 'waiting';
	    			break;
	    	}

		$this->load->view('match/board',$data);
    }

    function getTurn() {
 		$this->load->model('match_model');
		$user = $_SESSION['user'];
    	$cur_match = $this->match_model->get_cur_match_for_user($user->id);
    	$cur_state = unserialize($cur_match->board_state);

		$cur_board = $cur_state['board'];

    	$turn = false;
    	if ($user->id == $cur_match->user1_id) { //invited player
    		$turn = !$cur_state['hostTurn'];
    	}

    	if ($user->id == $cur_match->user2_id) { //host player
    		$turn = $cur_state['hostTurn'];
    	}

    	if ($turn) {
    		$m = "true";
    	} else {
    		$m = "false";
    	}

		echo json_encode(array('turn' => $turn, 'board' => $cur_board));
    }

    function setTurn() {

    }

    function postMove() {
    	$row = $this->input->post('row');
    	$col = $this->input->post('col');
		  $this->load->model('match_model');
		  $user = $_SESSION['user'];

    	$cur_match = $this->match_model->get_cur_match_for_user($user->id);
    	$cur_state = unserialize($cur_match->board_state);

    	$player_num = (($user->id == $cur_match->user1_id) ? 1: 2);

    	$cur_board = $cur_state['board'];
    	$cur_board[$row][$col] = $player_num;
      error_log($this->_checkWinner($cur_board, $row, $col, $player_num));

    	$cur_turn = !$cur_state['hostTurn'];

    	$update_state = array('board' => $cur_board, 'hostTurn' => $cur_turn);

    	$this->match_model->set_cur_board($cur_match->id, serialize($update_state));

    }

   function _checkWinner($board, $r, $c, $player) {

     if ($this->_checkVertical($board, $r, $c, $player)){
       return true;
     } else if ($this->_checkHorizontal($board, $r, $c, $player)){
       return true;
     } else if ($this->_checkDiagLeftRight($board, $r, $c, $player)) {
       //Checks from bottom left to top right
       return true;
     } else if ($this->_checkDiagRightLeft($board, $r, $c, $player)) {
       //Checks from bottom right to top left
       return true;
     }
     return false;
   }

   function _checkValidMove($row, $col) {
     if ($row <= 6 && $row > 0) {
         if ($col <= 7 && $col > 0) {
           return true;
         }
     }
     return false;
   }

   function _checkVertical($board, $r, $c, $player){
     $total = 0;
     $curR = $r;
     $curC = $c;

     //Check down
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curR++;
       } else {
         break;
       }
     }

  return ($total >= 4 ? true:false);
   }

   function _checkHorizontal($board, $r, $c, $player){
     $total = 0;
     $curR = $r;
     $curC = $c;

     //checkLeft
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curC--;
       } else {
         $curC = $c+1;
         break;
       }
     }

     //checkRight
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curC++;
       } else {
         break;
       }
     }

     return ($total >= 4 ? true:false);
   }

   function _checkDiagLeftRight($board, $r, $c, $player){
     $total = 0;
     $curR = $r;
     $curC = $c;

     //check towards bottom left
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curC--;
         $curR++;
       } else {
         // Switch Direction
         $curC = $c+1;
         $curR = $r-1;
         break;
       }
     }

     //check towards top right
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curC++;
         $curR--;
       } else {
         break;
       }
     }

     return ($total >= 4 ? true:false);
   }

   function _checkDiagRightLeft($board, $r, $c, $player){
     $total = 0;
     $curR = $r;
     $curC = $c;

     //check towards bottom right
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curC++;
         $curR++;
       } else {
         // Switch Direction
         $curC = $c-1;
         $curR = $r-1;
         break;
       }
     }

     //check towards top left
     while($this->_checkValidMove($curR, $curC)){
       if ($board[$curR][$curC] == $player){
         $total++;
         $curC--;
         $curR--;
       } else {
         break;
       }
     }

     return ($total >= 4 ? true:false);
   }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');

 		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');

 			$user = $_SESSION['user'];

 			$user = $this->user_model->getExclusive($user->login);
 			if ($user->user_status_id != User::PLAYING) {
				$errormsg="Not in PLAYING state";
 				goto error;
 			}

 			$match = $this->match_model->get($user->match_id);

 			$msg = $this->input->post('msg');

 			if ($match->user1_id == $user->id)  {
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}

 			echo json_encode(array('status'=>'success'));

 			return;
 		}

 		$errormsg="Missing argument";

		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}

	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');

 		$user = $_SESSION['user'];

 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode
 		$this->db->trans_begin();

 		$match = $this->match_model->getExclusive($user->match_id);

 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}

 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}

 		// if all went well commit changes
 		$this->db->trans_commit();

 		echo json_encode(array('status'=>'success','message'=>$msg));
		return;

		transactionerror:
		$this->db->trans_rollback();

		error:
		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}

 }

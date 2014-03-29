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
    	// if ($user->id == $cur_match->user1_id) {
    	// 	$turn = (($this->player_turn == 1) ? true : false);
    	// 	error_log("Setting for player 1 to: " . $turn);
    	// } else {
    	// 	$turn = (($this->player_turn == 2) ? true : false);
    	// 	error_log("Setting for player 2 to: " . $turn);
    	// }
    	// if ($_SESSION['turn'] == 1 && $this->player_turn == 1) {
    	// 	$turn = true;
    	// 	error_log("Player 1: " . $_SESSION['turn'] . " player_turn: " . $this->player_turn);
    	// } else if ($_SESSION['turn'] == 1 && $this->player_turn == 2) {
    	// 	$turn = false;
    	// }

    	// if ($_SESSION['turn'] == 2 && $this->player_turn == 2) {
    	// 	$turn = true;
    	// 	error_log("Player 2: " . $_SESSION['turn'] . " player_turn: " . $this->player_turn);
    	// } else if ($_SESSION['turn'] == 2 && $this->player_turn == 1) {
    	// 	$turn = false;
    	// }
    	if ($user->id == $cur_match->usr1_id) {
    		$turn = $cur_state['turn']['usr1'];
    		error_log("Player 1 " . $turn);
    	} else {
    		$turn = $cur_state['turn']['usr2'];   
    		error_log("Player 2 " . $turn); 		
    	}

		$cur_board = $cur_state['board'];
    	
    	error_log("Player " . $_SESSION['user']->id . " Turn var " . $turn);
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

    	$cur_turn = $cur_state['turn'];
    	error_log("****************** BEFORE " . $cur_turn['usr1']);
    	$cur_turn['usr1'] = (($cur_turn['usr1']) ? false : true);
    	$cur_turn['usr2'] = (($cur_turn['usr2']) ? false : true);
    	error_log("***************** AFTER " . $cur_turn['usr1']);
    	$update_state = array('board' => $cur_board, 'turn' => $cur_turn);

    	$this->match_model->set_cur_board($cur_match->id, serialize($update_state));

    	echo json_encode(array('board' => $cur_board));
    	
    	// $this->player_turn = (($this->player_turn == 1) ? 2 : 1);
    	// error_log("new player_turn: " . $this->player_turn);
    	// error_log("Val: " . $cur_board[$row][$col]);
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


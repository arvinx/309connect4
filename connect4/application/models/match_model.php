<?php
class Match_model extends CI_Model {
	
	function getExclusive($id)
	{
		$sql = "select * from `match` where id=? for update";
		$query = $this->db->query($sql,array($id));
		if ($query && $query->num_rows() > 0)
			return $query->row(0,'Match');
		else
			return null;
	}

	function get($id)
	{
		$this->db->where('id',$id);
		$query = $this->db->get('match');
		if ($query && $query->num_rows() > 0)
			return $query->row(0,'Match');
		else
			return null;
	}

	function get_cur_match_for_user($user_id) {
		$this->db->where('user1_id', $user_id);
		$this->db->or_where('user2_id', $user_id);
		$query = $this->db->get('match');
		if ($query && $query->num_rows() > 0) {
			return $query->row(0,'Match');
		} else {
			return null;
		}
	}
	
	function set_cur_board($id, $board) {
		$this->db->where('id',$id);
		return $this->db->update('match',array('board_state'=>$board));
	}
	
	function insert($match) {
		return $this->db->insert('match',$match);
	}

	function empty_table() {
		return $this->db->empty_table("`match`");
	}

	function updateMsgU1($id,$msg) {
		$this->db->where('id',$id);
		return $this->db->update('match',array('u1_msg'=>$msg));
	}
	
	function updateMsgU2($id,$msg) {
		$this->db->where('id',$id);
		return $this->db->update('match',array('u2_msg'=>$msg));
	}
	
	function updateStatus($id, $status) {
		$this->db->where('id',$id);
		return $this->db->update('match',array('match_status_id'=>$status));
	}
	
}
?>
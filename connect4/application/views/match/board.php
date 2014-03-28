<!doctype html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="<?= base_url() ?>/css/foundation.css" />
	<link rel="stylesheet" href="<?= base_url() ?>/css/candyshop.css" />
	<link rel="stylesheet" href="<?= base_url() ?>/css/board.css" />
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<script src="<?= base_url() ?>/js/vendor/modernizr.js"></script>
	<script src="<?= base_url() ?>/js/connect4.js"></script>
	<script>


	var otherUser = "<?= $otherUser->login ?>";
	var user = "<?= $user->login ?>";
	var status = "<?= $status ?>";

	$(function(){
		$('body').everyTime(2000,function(){
			if (status == 'waiting') {
				$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
					if (data && data.status=='rejected') {
						alert("Sorry, your invitation to play was declined!");
						window.location.href = '<?= base_url() ?>arcade/index';
					}
					if (data && data.status=='accepted') {
						status = 'playing';
						$('#status').html('Playing ' + otherUser);
					}

				});
			}
			var url = "<?= base_url() ?>board/getMsg";
			$.getJSON(url, function (data,text,jqXHR){
				if (data && data.status=='success') {
					var conversation = $('[name=conversation]').val();
					var msg = data.message;
					if (msg.length > 0)
						$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
				}
			});
		});

		$('form').submit(function(){
			var arguments = $(this).serialize();
			var url = "<?= base_url() ?>board/postMsg";
			$.post(url,arguments, function (data,textStatus,jqXHR){
				var conversation = $('[name=conversation]').val();
				var msg = $('[name=msg]').val();
				$('[name=conversation]').val(conversation + "\n" + user + ": " + msg);
			});
			return false;
		});

		setInterval(function() {
			$.getJSON('<?= base_url() ?>board/getTurn',function(data, text, jqZHR){
				turn(data.turn);
			});
		}, 200);

		function turn(myturn) {
			if (myturn) {
				$('.game-board').find('.disabled').attr('disabled', false);

			} else {
				// $('.game-board').find('*').attr('disabled', true);
			}
		}

		$('.grid').click(function() {
			var row_col = $(this).attr('id');
			var row = Number(row_col.charAt(0));
			var col = Number(row_col.charAt(1));
			var endpoint = "<?= base_url() ?>board/postMove";
			$.ajax({
				url: endpoint,
				type: 'POST',
				async: false,
				data: {"row": row, "col": col}
			});
			turn(false);
		});

	// var table_rows = $(".game-board").children().each(function(i, c) {
	// 	var cell_li = $(c);
	// 	var cell = cell_li.find("button");
	// 	var cell_content = cell.html();
	// 	if(cell.is('button')) {
	// 		var id_str = cell.attr('id');
	// 		var col = Number(id_str.charAt(0)); 
	// 		var row = Number(id_str.charAt(1));
	// 		// alert("col " + col + " row: " + row);
	// 	}
	// });

});

</script>
</head> 
<body> 
	<div class="row">
		<div class="medium-8 small-centered columns">
			<h1>Game Arena</h1>

			<div>
				<?php
				echo "<p>Hello " . $user->fullName() . anchor('account/logout','(Logout)') . "</p>";
				?>
			</div>
			<div id='status'> 
				<?php 
				if ($status == "playing")
					echo "<p>Playing " . $otherUser->login . "</p>";
				else
					echo "<p>Wating on " . $otherUser->login . "</p>";
				?>
			</div>

			<br><br>
			<div id='game-board'>
				<div class="large-12 columns">
					<div class="row-centered">
						<ul class="button-group game-board">
							<li><button id='11' class="button grid disabled" disabled>O</button></li>
							<li><button id='12' class="button grid disabled" disabled>O</button></li>
							<li><button id='13' class="button grid disabled" disabled>O</button></li>
							<li><button id='14' class="button grid disabled" disabled>O</button></li>
							<li><button id='15' class="button grid disabled" disabled>O</button></li>
							<li><button id='16' class="button grid disabled" disabled>O</button></li>
							<li><button id='17' class="button grid disabled" disabled>O</button></li>
							<br>
							<li><button id='21' class="button grid disabled" disabled>O</button></li>
							<li><button id='22' class="button grid disabled" disabled>O</button></li>
							<li><button id='23' class="button grid disabled" disabled>O</button></li>
							<li><button id='24' class="button grid disabled" disabled>O</button></li>
							<li><button id='25' class="button grid disabled" disabled>O</button></li>
							<li><button id='26' class="button grid disabled" disabled>O</button></li>
							<li><button id='27' class="button grid disabled" disabled>O</button></li>
							<br>							
							<li><button id='31' class="button grid disabled" disabled>O</button></li>
							<li><button id='32' class="button grid disabled" disabled>O</button></li>
							<li><button id='33' class="button grid disabled" disabled>O</button></li>
							<li><button id='34' class="button grid disabled" disabled>O</button></li>
							<li><button id='35' class="button grid disabled" disabled>O</button></li>
							<li><button id='36' class="button grid disabled" disabled>O</button></li>
							<li><button id='37' class="button grid disabled" disabled>O</button></li>
							<br>							
							<li><button id='41' class="button grid disabled" disabled>O</button></li>
							<li><button id='42' class="button grid disabled" disabled>O</button></li>
							<li><button id='43' class="button grid disabled" disabled>O</button></li>
							<li><button id='44' class="button grid disabled" disabled>O</button></li>
							<li><button id='45' class="button grid disabled" disabled>O</button></li>
							<li><button id='46' class="button grid disabled" disabled>O</button></li>
							<li><button id='47' class="button grid disabled" disabled>O</button></li>
							<br>							
							<li><button id='51' class="button grid disabled" disabled>O</button></li>
							<li><button id='52' class="button grid disabled" disabled>O</button></li>
							<li><button id='53' class="button grid disabled" disabled>O</button></li>
							<li><button id='54' class="button grid disabled" disabled>O</button></li>
							<li><button id='55' class="button grid disabled" disabled>O</button></li>
							<li><button id='56' class="button grid disabled" disabled>O</button></li>
							<li><button id='57' class="button grid disabled" disabled>O</button></li>
							<br>							
							<li><button id='61' class="button grid">O</button></li>
							<li><button id='62' class="button grid">O</button></li>
							<li><button id='63' class="button grid">O</button></li>
							<li><button id='64' class="button grid">O</button></li>
							<li><button id='65' class="button grid">O</button></li>
							<li><button id='66' class="button grid">O</button></li>
							<li><button id='67' class="button grid">O</button></li>
							<br>
						</ul>
					</div>


				</div>

			</div>

			<h4>Messaging</h4>
			<?php 

			echo form_textarea('conversation');

			echo form_open();
			echo form_input('msg');
			echo form_submit('Send','Send');
			echo form_close();

			?>


		</div>
	</div>
	<script src="<?= base_url() ?>/js/foundation.min.js"></script>
	<script>
	$(document).foundation();
	</script>
</body>

</html>


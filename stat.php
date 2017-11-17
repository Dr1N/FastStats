<!DOCTYPE html>
<html>
<head>
	<script>var isServer = false</script>
	<script><?php echo "isServer = true;"; ?></script>
	<title>Fast Statistic</title>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<div class="container hidden">
		<div class="row">
			<h1 class="text-center">STATISTIC:</h1>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script>
		$(function() {
			if (isServer === false) {
				console.log("Запусти скрипт на сервере!");
				$('body').html("Запусти скрипт на сервере!");
			} else {
				$('.container').removeClass("hidden");
			}
		});
	</script>
</body>
</html>
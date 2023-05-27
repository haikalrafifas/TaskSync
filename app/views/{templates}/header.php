<!DOCTYPE html>
<html>
<head>
	<title><?= $title ?></title>
	<script src="/assets/plugins/jquery/jquery.min.js"></script>
	<script src="/assets/plugins/js-cookie/js-cookie.min.js"></script>
	<style>
		.card-container {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
		}

		.card {
		width: calc(33.33% - 20px); /* Adjust the width as needed */
		margin-bottom: 20px; /* Adjust the margin as needed */
		display: flex;
		flex-direction: column;
		}

		.card-image {
		height: 200px; /* Adjust the height of the image */
		background-size: cover;
		background-position: center;
		}

		.card-content {
		padding: 10px;
		background-color: #f5f5f5; /* Adjust the background color as needed */
		}

		.card-title {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px; /* Adjust the margin as needed */
		}

		.card-description {
		font-size: 14px;
		}
	</style>
</head>
<body>
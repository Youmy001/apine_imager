<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	
	<title><?= $this->_title ?></title>

	<!-- Bootstrap core CSS -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<style type="text/css">
		body {padding-top: 20px;padding-bottom:20px}
		.header {
			padding-right: 0px;
			padding-left: 0px;
			padding-bottom: 20px;
			margin-bottom: 30px;
			border-bottom: 1px solid #e5e5e5;
		}
		.header h3 {
			margin-top: 0;
			margin-bottom: 0;
			line-height: 40px;
		}
	</style>
</head>

<body>
	<div class="container">
	  <div class="header clearfix">
		<h3 class="text-muted">APIne Framework</h3>
	  </div>

		<?php include_once("$this->_view.php");?>

	</div>
	
	<script src="https://code.jquery.com/jquery-1.12.2.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.4.0/knockout-min.js"></script>
	<script>
	function ImportViewModel () {
		
	}

	function InstallerViewModel () {
		var self = this;
		self.step_zero_visible = ko.observable(true);
		self.step_one_visible = ko.observable(false);
		self.step_two_visible = ko.observable(false);
		self.step_three_visible = ko.observable(false);
		self.step_four_visible = ko.observable(false);
		self.step_five_visible = ko.observable(false);
		self.step_six_visible = ko.observable(false);
		self.step_finish_visible = ko.observable(false);
		self.step_error_visible = ko.observable(false);
		
		self.app_name = ko.observable("");
		self.app_auth = ko.observable("");
		self.app_desc = ko.observable("");
		
		self.db_host = ko.observable("");
		self.db_type = ko.observable('mysql');
		self.db_name = ko.observable("");
		self.db_char = ko.observable('utf8');
		self.db_user = ko.observable("");
		self.db_pass = ko.observable("");
		self.invalid_step_two = ko.observable(false);
		
		self.loc_time = ko.observable();
		self.loc_lang = ko.observable();
		
		self.email = ko.observable();
		self.email_host = ko.observable("");
		self.email_port = ko.observable("");
		self.email_prot = ko.observable("");
		self.email_auth = ko.observable("");
		self.email_user = ko.observable("");
		self.email_pass = ko.observable("");
		self.email_name = ko.observable("");
		self.email_addr = ko.observable("");

		self.generate = ko.observable();
		
		self.email_auth_text = ko.computed(function() {
			return (self.email_auth() == 1) ? 'Yes' : 'No';
		}, this);
		
		self.email_auth_bool = ko.computed(function() {
			return (self.email_auth() == 1) ? true : false;
		}, this);

		self.generate_text = ko.computed(function () {
			return (self.generate() == 1) ? 'Yes' : 'No';
		}, this);

		self.generate_bool = ko.computed(function () {
			return (self.generate() == 1) ? true : false;
		}, this);
		
		self.show_step = function (step_number) {
			self.step_zero_visible(false);
			self.step_one_visible(false);
			self.step_two_visible(false);
			self.step_three_visible(false);
			self.step_four_visible(false);
			self.step_five_visible(false);
			self.step_six_visible(false);
			
			switch (step_number) {
				case 1:
					self.step_one_visible(true);
					break;
				case 2:
					self.step_two_visible(true);
					break;
				case 3:
					self.step_three_visible(true);
					break;
				case 4:
					self.step_four_visible(true);
					break;
				case 5:
					self.step_five_visible(true);
					break;
				case 6:
					self.step_six_visible(true);
					break;
				case 0:
				default:
					self.step_zero_visible(true);
			}
		}
			
		self.show_next_step = function () {
			if (self.step_zero_visible()) {
				self.step_zero_visible(false);
				self.step_one_visible(true);
			} else if (self.step_one_visible()) {
				self.step_one_visible(false);
				self.step_two_visible(true);
			} else if (self.step_two_visible()) {
				self.step_two_visible(false);
				self.step_three_visible(true);
			} else if (self.step_three_visible()) {
				self.step_three_visible(false);
				self.step_four_visible(true);
			} else if (self.step_four_visible()) {
				self.step_four_visible(false);
				self.step_five_visible(true);
			} else if (self.step_five_visible()) {
				self.step_five_visible(false);
				self.step_six_visible(true);
			}
		};
		
		self.validate_step_one = function (element) {
			self.step_one_visible(false);
			self.step_two_visible(true);
		};
		
		self.validate_step_two = function (element) {
			json_array = new Object();
			json_array.host = self.db_host();
			json_array.type = self.db_type();
			json_array.char = self.db_char();
			json_array.name = self.db_name();
			json_array.user = self.db_user();
			json_array.pass = self.db_pass();
	
			//console.log(json_array);
			$.ajax("/install/test_database", {
				data: JSON.stringify(json_array),
				type: "post", contentType: "application/json",
				success: function (result) {
					self.step_two_visible(false);
					self.step_three_visible(true);
				 },
				error: function () {
					self.invalid_step_two(true);
				},
				fail: function () {
					self.invalid_step_two(true);
				}
			});
		};

		self.apply_settings = function () {
			json_object = new Object();
			app_object = new Object();
			database_object = new Object();
			locale_object = new Object();
			email_object = new Object();
			
			app_object.title = self.app_name();
			app_object.author = self.app_auth();
			app_object.description = self.app_desc();
			json_object.application = app_object;
			
			database_object.host = self.db_host();
			database_object.type = self.db_type();
			database_object.dbname = self.db_name();
			database_object.charset = self.db_char();
			database_object.username = self.db_user();
			database_object.password = self.db_pass();
			json_object.database = database_object;
			
			locale_object.timezone_default = self.loc_time();
			locale_object.locale_default = self.loc_lang();
			locale_object.locale_detection = 'yes';
			locale_object.locale_cookie = 'no';
			json_object.localization = locale_object;
			
			if (self.email() == 1) {
				email_object.host = self.email_host();
				email_object.port = self.email_port();
				email_object.protocol = self.email_prot();
				email_object.smtp_auth = self.email_auth();
				
				if (self.email_auth_bool()) {
					email_object.smtp_username = self.email_user();
					email_object.smtp_password = self.email_pass();
				}
				
				email_object.sender_name = self.email_name();
				email_object.sender_address = self.email_addr();
				json_object.mail = email_object();
			}

			json_object.generate = self.generate_bool();
			
			//console.log(JSON.stringify(json_object));
			$.ajax("/install/apply_new_config", {
				data: JSON.stringify(json_object),
				type: "post", contentType: "application/json",
				success: function (result) {
					self.step_six_visible(false);
					self.step_finish_visible(true);
				 },
				error: function () {
					self.step_six_visible(false);
					self.step_error_visible(true);
				},
				fail: function () {
					self.step_six_visible(false);
					self.step_error_visible(true);
				}
			});
		};
		
	}

	ko.applyBindings(new InstallerViewModel());
	</script>
</body>
</html>
<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="/studyplanner">Study Planner</a>
					<div class="nav-collapse collapse">
						<ul class="nav">
						  <li class="active"><a href="/studyplanner">Home</a></li>
						  <li><a href="#">About</a></li>
						  <li><a href="#">Contact</a></li>
						  <li class="divider-vertical"></li>
						  <li class="dropdown">
						  
			 <?php
				$member = substr($_SERVER["REDIRECT_unscoped_affiliation"],7);
				$uid = $_SERVER["REDIRECT_uid"];
				$arrAdmin = array("nyahya", "mabrahim", "kkaspero", "rsalas", "usivagur");

				if($member == "student")
				{
					if(in_array($uid, $arrAdmin))
					{
										
			?>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Switch to.. <b class="caret"></b></a>
							<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
								<li><a href="/studyplanner/admin">Administrator view</a></li>
								<li><a href="/studyplanner/student">Student view</a></li>
							</ul>
						 </li>
			<?php									
					}
				}
				else
				{			
			?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Switch to.. <b class="caret"></b></a>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
						<li><a href="/studyplanner/admin">Administrator view</a></li>
						<li><a href="/studyplanner/student">Student view</a></li>
					</ul>
				 </li>
			<?php	
				}
			?>
						</ul>
						<p class="navbar-text pull-right">
							<a href="#" class="navbar-link"><strong><?php echo $_SERVER["REDIRECT_displayName"]; ?></strong></a>
						</p>
					</div>
				</div>
			</div>
		</div>

<?php
$datatable = new Datatable_List();
$distributors = $datatable->getAllDistributorWithRewards('mlm_users');
$condition = "id = '".get_current_user_id()."'";
$user = $datatable->get_all_cond_data('mlm_users',$condition);
?>
<div class="jhRf">
    <div class="pre-loader" style="display: none">
        <div class="overlay"></div>

        <div class='loader-container'>
            <div class="prespinner"></div>
        </div>
    </div>
	<div class="trR">
		<ul>
			<li><a href="<?= get_admin_url().'admin.php?page=mlm-distributor-panel'; ?>">distributor panel</a></li>
			<li><a href="<?= get_admin_url().'admin.php?page=mlm-commodity-circulation-panel'; ?>">Commodity Circulation Panel</a></li>
			<li class="act"><a href="<?= get_admin_url().'admin.php?page=mlm-structure-panel'; ?>">Structure Panel</a></li>
            <li><a href="<?= get_admin_url().'admin.php?page=mlm-rewards-history-panel'; ?>">Rewards History</a></li>
        </ul>
	</div>
	<div class="ttbcs">
		<ul>
			<li class="aatabs"><a data-id="family-tree" class="ttabsa">Family Tree</a></li>
			<li><a data-id="structure-panel" class="ttabsa">Structure Panel</a></li>
		</ul>
		<div class="aaEbs">
			<form action="" method="GET">
				<input type="search" name="search_mlmuser" placeholder="Search User..">
				<input type="hidden" name="page" value="mlm-structure-panel">
				<input type="submit" value="Search">
			</form>
		</div>
	</div>

	<div id="family-tree" class="tavsect">
		<div class="eendtree_wrap">

		<div class="tree dsG">

			<ul>

			  <li>

				<div class="family">

					<div class="person child male mngs" style="background: #456990;color: #fff; margin-bottom: 10px">

						<div class="name">Management</div>

					</div>

			    <div class="parent">
			    	<?php if(isset($_GET['search_mlmuser'])){ ?>
			    		<div class="person female" style="background: #F45B69;color: #fff;"> 
			    			<?php $uid = $_GET['search_mlmuser']; ?>
					        <div class="name"><?= get_user_name($uid); ?></div>
					        <div class="das">
					        	<span><?= $uid; ?></span>
					        	<span><?= get_user_rank($uid); ?></span>
					        </div>
					    </div>
			    	<?php }else{ ?>
			    		<div class="person female" style="background: #F45B69;color: #fff;">
					        <div class="name"><?= $user[0]->user_name; ?></div>
					        <div class="das">
					        	<?php $uid = $user[0]->unique_id; ?>
					        	<span><?= $uid; ?></span>
					        	<span><?= $user[0]->rank; ?></span>
					        </div>
					    </div>

			    	<?php } ?>

			      <?php 

			      	$single = '';

			      	$end = '';

			      ?>

			      <?php if(count(get_mlm_children($uid)) > 0){ 

			      	if(count(get_mlm_children($uid)) == 1){ $single = 'single_cl'; }

			      	?>

			      		<ul>

			      		<?php foreach (get_mlm_children($uid) as $ndch) {

			      			if(count(get_mlm_children($ndch->unique_id)) == 0){ $end = 'endcl_cl'; }

			      		 ?>

      					<li>

				          <div class="family <?php if($end != ''){ echo $single; } ?>">

				            <div class="parent <?= $end; ?>">

				              <div class="person male">

				                <div class="name"><?= $ndch->user_name; ?></div>

				                <div class="das">

						        	<span><?= $ndch->unique_id; ?></span>

						        	<span><?= $ndch->rank; ?></span>

						        </div>

				              </div>

				              <?php 

						      	$single = '';

						      	$end = '';

						      ?>

				              <?php if(count(get_mlm_children($ndch->unique_id)) > 0){ 

				              	if(count(get_mlm_children($ndch->unique_id)) == 1){ $single = 'single_cl'; }

				              	?>

				              <ul>

				              	<?php foreach (get_mlm_children($ndch->unique_id) as $rdnc) { 

				              		if(count(get_mlm_children($rdnc->unique_id)) == 0){ $end = 'endcl_cl'; }

				              		?>

				                <li>

				                	<div class="family <?php if($end != ''){ echo $single; } ?>">

				                		<div class="parent <?= $end; ?>">

						                  <div class="person child male">

						                    <div class="name"><?= $rdnc->user_name; ?></div>

						                    <div class="das">

									        	<span><?= $rdnc->unique_id; ?></span>

									        	<span><?= $rdnc->rank; ?></span>

									        </div>

						                  </div>

						                  <?php 

									      	$single = '';

									      	$end = '';

									      ?>

						                  <?php if(count(get_mlm_children($rdnc->unique_id)) > 0){ 

						                  	if(count(get_mlm_children($rdnc->unique_id)) == 1){ $single = 'single_cl'; }

						                  	?>

						                  	<ul>

						                  	  <?php foreach (get_mlm_children($rdnc->unique_id) as $fortch) {

						                  	  	if(count(get_mlm_children($fortch->unique_id)) == 0){ $end = 'endcl_cl'; }

						                  	   ?>

						                  	  	<li class="<?= count(get_mlm_children($fortch->unique_id)); ?>">

								                  <div class="family <?php if($end != ''){ echo $single; } ?>">

								                  	<div class="parent <?= $end; ?>">

								                  	  <div class="person child male">

						                    			<div class="name"><?= $fortch->user_name; ?></div>

						                    			<div class="das">

												        	<span><?= $fortch->unique_id; ?></span>

												        	<span><?= $fortch->rank; ?></span>

												        </div>

						                  			  </div>

						                  			   <?php 

												      	 $single = '';

												      	 $end = '';

												       ?>

						                  			  <?php if(count(get_mlm_children($fortch->unique_id)) > 0){

						                  			  	if(count(get_mlm_children($fortch->unique_id)) == 1){ $single = 'single_cl'; }

						                  			    ?>

						                  			    <ul>

						                  			    	<?php foreach (get_mlm_children($fortch->unique_id) as $fift) { 

						                  			    	if(count(get_mlm_children($fift->unique_id)) == 0){ $end = 'endcl_cl'; }

						                  			    		?>

						                  			    		<li>

						                  			    			<div class="family <?php if($end != ''){ echo $single; } ?>">

						                  			    				<div class="parent <?= $end; ?>">

						                  			    					 <div class="person child male">

															                    <div class="name"><?= $fift->user_name; ?></div>

															                    <div class="das">

																		        	<span><?= $fift->unique_id; ?></span>

																		        	<span><?= $fift->rank; ?></span>

																		        </div>

																		      </div>

																		      <?php 

																		      	 $single = '';

																		      	 $end = '';

																		       ?>

																		        <?php if(count(get_mlm_children($fift->unique_id)) > 0){ 

						                  										if(count(get_mlm_children($fift->unique_id)) == 1){ $single = 'single_cl'; } ?>

								                  			    				<ul>

								                  			    					<?php foreach (get_mlm_children($fift->unique_id) as $sisxth) { 

						                  			    							if(count(get_mlm_children($sisxth->unique_id)) == 0){ $end = 'endcl_cl'; } ?>

						                  			    							<li>

						                  			    							   <div class="family <?php if($end != ''){ echo $single; } ?>">

						                  			    							   	 <div class="parent <?= $end; ?>">

						                  			    							   	 	<div class="person child male">

																                    			<div class="name"><?= $sisxth->user_name; ?></div>

																                    			<div class="das">

																						        	<span><?= $sisxth->unique_id; ?></span>

																						        	<span><?= $sisxth->rank; ?></span>

																						        </div>

																                  			</div>

																                  			 <?php 

																						      	 $single = '';

																						      	 $end = '';

																						       ?>

																                  			<?php if(count(get_mlm_children($sisxth->unique_id)) > 0){ 

						                  													if(count(get_mlm_children($sisxth->unique_id)) == 1){ $single = 'single_cl'; } ?>

						                  													<ul>

						                  														<?php foreach (get_mlm_children($sisxth->unique_id) as $sevnth) {

						                  			    										if(count(get_mlm_children($sevnth->unique_id)) == 0){ $end = 'endcl_cl'; }

						                  			    										?>

						                  			    										<li>

						                  			    											 <div class="family <?php if($end != ''){ echo $single; } ?>">

						                  			    							   	 				<div class="parent <?= $end; ?>">

						                  			    							   	 					<div class="person child male">

																				                    			<div class="name"><?= $sevnth->user_name; ?></div>

																				                    			<div class="das">

																										        	<span><?= $sevnth->unique_id; ?></span>

																										        	<span><?= $sevnth->rank; ?></span>

																										        </div>

																				                  			</div>

																				                  			<?php 

																									      	 $single = '';

																									      	 $end = '';

																									       ?>

																				                  			<?php if(count(get_mlm_children($sevnth->unique_id)) > 0){ 

						                  																		if(count(get_mlm_children($sevnth->unique_id)) == 1){ $single = 'single_cl'; } ?>

						                  																		<ul>

						                  																			<?php foreach (get_mlm_children($sevnth->unique_id) as $eigth) {

											                  			    										if(count(get_mlm_children($eigth->unique_id)) == 0){ $end = 'endcl_cl'; }

											                  			    										?>

											                  			    										<li>

											                  			    											<div class="family <?php if($end != ''){ echo $single; } ?>">

						                  			    							   	 									<div class="parent <?= $end; ?>">

						                  			    							   	 										<div class="person child male">

																									                    			<div class="name"><?= $eigth->user_name; ?></div>

																									                    			<div class="das">

																															        	<span><?= $eigth->unique_id; ?></span>

																															        	<span><?= $eigth->rank; ?></span>

																															        </div>

																									                  			</div>

																									                  			<?php 

																															      	 $single = '';

																															      	 $end = '';

																															       ?>

																														            <?php if(count(get_mlm_children($eigth->unique_id)) > 0){ 

											                  																		if(count(get_mlm_children($eigth->unique_id)) == 1){ $single = 'single_cl'; } ?>

											                  																		<ul>

											                  																			<?php foreach (get_mlm_children($eigth->unique_id) as $ninth) {

																	                  			    										if(count(get_mlm_children($ninth->unique_id)) == 0){ $end = 'endcl_cl'; } ?>

																	                  			    										<li>

																	                  			    											<div class="family <?php if($end != ''){ echo $single; } ?>">

																	                  			    												<div class="parent <?= $end; ?>">

																	                  			    													<div class="person child male">

																															                    			<div class="name"><?= $ninth->user_name; ?></div>

																															                    			<div class="das">

																																					        	<span><?= $ninth->unique_id; ?></span>

																																					        	<span><?= $ninth->rank; ?></span>

																																					        </div>

																															                  			</div>

																	                  			    												</div>

																	                  			    											</div>

																	                  			    										</li>

																	                  			    									<?php } ?>

											                  																		</ul>

											                  																	<?php } ?>

						                  			    							   	 									</div>

						                  			    							   	 								</div>

											                  			    										</li>

											                  			    									<?php } ?>

						                  																		</ul>

						                  																	<?php } ?>

						                  			    							   	 				</div>

						                  			    							   	 			</div>

						                  			    										</li>

						                  			    										<?php } ?>

						                  													</ul>

						                  												<?php } ?>

						                  			    							   	 </div>

						                  			    							   </div>

						                  			    							</li>

						                  			    						<?php } ?>

								                  			    				</ul>

								                  			    			<?php } ?>

						                  			    				</div>

						                  			    			</div>

						                  			    		</li>

						                  			    	<?php } ?>

						                  			    </ul>

						                  			  <?php } ?>

								                  	  </div>

								                    </div>

							                  	</li>

							                  <?php } ?>

							                </ul>

							            <?php } ?>

					              		</div>

				             		</div>

				                </li>

				            <?php } ?>

				              </ul>

				          <?php } ?>

				            </div>			            

				          </div>

				        </li>

		  			<?php } ?>

		  			</ul>

		  			<?php } ?>

			    </div>

			  </div>

			</li>

			</ul>

		</div>

	</div>
	</div>
	<div id="structure-panel" class="tavsect dsplnon">
		<table id="structure-table" class="ui celled table" style="width:100%">
			<thead>
				<tr>
					<th>Sl no.</th>
					<th>Distributor ID</th>
					<th>Distributor Name</th>
                    <th>Rank</th>
                    <th>Sponsor ID</th>
                    <th>Sponsor Name</th>
					<th>PCC</th>
					<th>SCC</th>
					<th>DR</th>
					<th>SR</th>
					<th>MR</th>
                    <th>BR</th>
                    <th>ALLR</th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 1; foreach ($distributors as $distributor) { ?>
					<tr>
						<td><?= $i; ?></td>
						<td><?= $distributor->unique_id; ?></td>
						<td><?= $distributor->user_name; ?></td>
                        <td><?= $distributor->rank; ?></td>
                        <td><?= $distributor->sponsor_id; ?></td>
                        <td><?= get_user_name($distributor->sponsor_id); ?></td>
						<td><?= $distributor->pcc; ?></td>
						<td><?= $distributor->scc; ?></td>
						<td><?= $distributor->dr; ?></td>
						<td><?= $distributor->sr; ?></td>
						<td><?= $distributor->mr; ?></td>
						<td><?= $distributor->br; ?></td>
						<td><?= (float)$distributor->dr + (float)$distributor->sr + (float)$distributor->mr ?></td>
					</tr>
				<?php $i++; } ?>
			</tbody>
			<tfoot>
				<tr>
					<th>Sl no.</th>
					<th>Distributor ID</th>
					<th>Distributor Name</th>
                    <th>Rank</th>
                    <th>Sponsor ID</th>
                    <th>Sponsor Name</th>
					<th>PCC</th>
					<th>SCC</th>
					<th>DR</th>
					<th>SR</th>
					<th>MR</th>
					<th>BR</th>
					<th>ALLR</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<script>
	jQuery('#structure-table').DataTable({
        "lengthMenu": [[10,], [10, "All"]]
    });
	jQuery(document).ready(function(){
		jQuery('.ttabsa').click(function(){
			jQuery('.ttbcs ul li').removeClass('aatabs');
			var id = jQuery(this).data('id');
			jQuery(this).parent().addClass('aatabs');
			jQuery('.tavsect').addClass('dsplnon');
			jQuery('#'+id).removeClass('dsplnon');
		})
	});
</script>
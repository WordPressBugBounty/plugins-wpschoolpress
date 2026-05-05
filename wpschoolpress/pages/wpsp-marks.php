<?php
if (!defined( 'ABSPATH' ) )exit('No Such File');
wpsp_header();
if( is_user_logged_in() ) {
	global $current_user, $wpdb;
	$current_user_role=$current_user->roles[0];
	if( $current_user_role=='administrator' || $current_user_role=='teacher' ) {
		wpsp_topbar();
		wpsp_sidebar();
		wpsp_body_start();
		$settings_data = [];
		$class_id       =   $subject_id =   $exam_id=0;
		$proversion     =   wpsp_check_pro_version();
		$proclass       =   !$proversion['status'] && isset( $proversion['class'] )? $proversion['class'] : '';
		$protitle       =   !$proversion['status'] && isset( $proversion['message'] )? $proversion['message']   : '';
		$prodisable     =   !$proversion['status'] ? 'disabled="disabled"'  : '';
		if( isset(  $_POST['MarkAction']  ) ){
			$class_id   =   (isset($_POST['ClassID'])) ?  intval($_POST['ClassID']) : '';
			$subject_id =   (isset($_POST['SubjectID'])? intval($_POST['SubjectID']) : '');
			$exam_id    =   (isset($_POST['ExamID'])? intval($_POST['ExamID']) : '');
		}
		$ctname     =   $wpdb->prefix.'wpsp_class';
		$sbname     =   $wpdb->prefix.'wpsp_subject';
		$classQuery =   "select `cid`,`c_name` from `$ctname`";
		$clt    =   $wpdb->get_results( $classQuery );
		$msg        =   'Please Add Class Before Adding Marks';
		if( $current_user_role=='teacher' ) {
			$cuserId    =   intval($current_user->ID);
			$classQuery = $wpdb->prepare("SELECT DISTINCT c.cid,c.c_name FROM $ctname c INNER JOIN $sbname s ON s.class_id= c.cid WHERE s.sub_teach_id = %d",$cuserId);
			$clt    =   $wpdb->get_results( $classQuery );
			$msg        =   'Please ask Principal to assign class and subject';
		}
		
		$wpsp_settings_table    =   $wpdb->prefix."wpsp_settings";
		$wpsp_settings_edit     =   $wpdb->get_results("SELECT * FROM $wpsp_settings_table" );
		foreach( $wpsp_settings_edit as $sdat ) {
			$settings_data[$sdat->option_name]  =   $sdat->option_value;
		}
		?>
		<div class="wpsp-card">
			<div class="wpsp-card-head">
				<h3 class="wpsp-card-title"><?php echo esc_html(apply_filters( 'wpsp_student_marks_heading_item',"Students Marks"),"wpschoolpress"); ?></h3>
			</div>
			<div class="wpsp-card-body">
				<?php
				$item =  apply_filters( 'wpsp_student_marks_title_item',esc_html("Class Name","wpschoolpress"));
			 	if( empty( $clt ) ) {
					echo '<div class="wpsp-text-red col-lg-2">'.esc_html($msg).'</div>';
				} else { ?>
					<form class="wpsp-form-horizontal" id="MarkForm" action="" method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'MarksRegister', 'wps_marks_nonce', '', true ); ?>		
						<div class="wpsp-row">
							<div class="wpsp-col-lg-3 wpsp-col-md-4 wpsp-col-sm-4 wpsp-col-xs-12">
								<div class="wpsp-form-group">
									<label class="wpsp-label"><?php esc_html_e("Class","wpschoolpress"); ?></label>
									<select name="ClassID"  id="ClassID" class="wpsp-form-control" required>
										<option value=""><?php esc_html_e( 'Select Class', 'wpschoolpress' ); ?> </option>
										<?php foreach( $clt as $cnm ) { ?>
                                            <option value="<?php echo esc_attr(intval($cnm->cid));?>" <?php if($cnm->cid==$class_id) echo esc_html("selected","wpschoolpress");?>><?php echo esc_html($cnm->c_name);?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="wpsp-col-lg-3 wpsp-col-md-4 wpsp-col-sm-4 wpsp-col-xs-12">
								<div class="wpsp-form-group">
									<label class="wpsp-label">
										<?php esc_html_e("Exam","wpschoolpress");?></label>
									<select name="ExamID" class="wpsp-form-control" id="ExamID" required>
										<?php
										if( $exam_id > 0 ) {
											$examtable  =   $wpdb->prefix.'wpsp_exam';
											$examlist = $wpdb->get_results($wpdb->prepare("SELECT eid,e_name FROM $examtable WHERE classid = %d",$class_id));
											foreach( $examlist as $exam ) { ?>
												<option value="<?php echo esc_attr(intval($exam->eid));?>" <?php if($exam->eid==$exam_id) echo esc_html("selected","wpschoolpress");?>><?php echo esc_html($exam->e_name);?></option>
										<?php }
										} else { ?>
											<option value=""><?php esc_html_e( 'Select Exam', 'wpschoolpress' ); ?> </option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="wpsp-col-lg-3 wpsp-col-md-4 wpsp-col-sm-4 wpsp-col-xs-12">
								<div class="wpsp-form-group">
									<label class="wpsp-label"><?php esc_html_e("Subject","wpschoolpress");?></label>
									<?php
									$examtable  =   $wpdb->prefix.'wpsp_exam';
									if( $exam_id!= '' ) {
                                        $exam_id = esc_sql($exam_id);
										$subjectID = $wpdb->get_var($wpdb->prepare("SELECT subject_id FROM $examtable WHERE eid = %d",$exam_id));
										$subjectlist    =   explode( ",", $subjectID );
									}
                                    ?>
									<select name="SubjectID"  id="SubjectID" class="wpsp-form-control" required>
										<?php if( $subject_id>0 ) {
                                            $sub_tbl    =   $wpdb->prefix."wpsp_subject";
											$subInfo = $wpdb->get_results($wpdb->prepare("SELECT sub_name,id FROM $sub_tbl WHERE class_id = %d",$class_id));
                                            foreach( $subInfo as $sub_list ) {
                                                if( in_array( $sub_list->id, $subjectlist ) ) {
                                                    echo "<option value='".esc_attr($sub_list->id)."'". selected( $subject_id, $sub_list->id, false ).">".esc_html($sub_list->sub_name)."</option>";
                                                }
											}
										} else { ?>
											<option value=""><?php esc_html_e( 'Select Subject', 'wpschoolpress' ); ?></option>
									<?php } ?>
									</select>
								</div>
							</div>
							<div class="wpsp-col-lg-3 wpsp-col-md-4 wpsp-col-sm-4 wpsp-col-xs-12 <?php echo esc_attr($proclass);?>" title="" <?php echo esc_html($prodisable); ?>>
								<div class="wpsp-form-group">
									<label class="wpsp-label"><?php esc_html_e( 'Attach CSV', 'wpschoolpress'); ?></label>
									<span <?php if($proversion['status'] != "1") {?> wpsp-tooltip="<?php echo esc_attr($protitle);?>" <?php } ?>>
										<div class="wpsp-btn wpsp-btn-file" <?php echo esc_html($prodisable); ?>>
											<span><i class="fa fa-file-text-o"></i> <?php esc_html_e( 'Attach CSV File', 'wpschoolpress' );?></span>
											<input type="file" name="MarkCSV" class="<?php echo esc_attr($proclass);?> wpsp-form-control" title="" <?php echo esc_html($prodisable); ?>>
										</div>
									</span>
									<span class="text"></span>
								</div>
							</div>
							<div class="clearfix"></div>
							<div class="wpsp-col-sm-8">
								<div class="wpsp-form-group">
									<button type="submit" class="wpsp-btn wpsp-btn-success MarkAction update-btn" name="MarkAction"  value="Add Marks"><?php esc_html_e( 'Add/Update', 'wpschoolpress'); ?> </button>
									<span <?php if($proversion['status'] != "1") {?> wpsp-tooltip="<?php echo esc_attr($protitle);?>" <?php } ?>>
										<button type="submit" name="MarkAction" class="wpsp-btn wpsp-dark-btn update-btn MarkAction <?php echo esc_attr($proclass);?>" <?php echo esc_html($prodisable); ?> value="ImportCSV"><?php esc_html_e( 'Upload CSV', 'wpschoolpress'); ?></button>
									</span>
									<button name="MarkAction" class="wpsp-btn wpsp-btn-primary update-btn" id="viewmarks" value="View Marks"><?php esc_html_e( 'View Marks', 'wpschoolpress'); ?> </button>
								</div>
							</div>
						</div>
					</form>
					<?php
					if(isset($_POST['MarkAction']) && sanitize_text_field($_POST['MarkAction'])=='Add Marks'){
						$mark_entered   =   '';
						//Get Extra Fields
						$extra_tbl      =   $wpdb->prefix."wpsp_mark_fields";
						$extra_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM $extra_tbl WHERE subject_id = %d",$subject_id));
						if( wpsp_IsMarkEntered( $class_id,$subject_id,$exam_id ) ) {
							$wpsp_marks     =   wpsp_GetMarks($class_id,$subject_id,$exam_id);
							$mark_entered   =   1;
							$wpsp_exmarks   =   wpsp_GetExMarks($subject_id,$exam_id);
							foreach($wpsp_exmarks as $exmark){
								$extra_marks[$exmark->student_id][$exmark->field_id]=$exmark->mark;
							}
						}
						?>
						<div id="mark_entry" class="col-md-12 col-lg-12 col-sm-12">
							<?php if( $mark_entered ==1 ) { ?>
								<h3 class="wpsp-card-title"><?php esc_html_e( 'Marks Already Entered update here!', 'wpschoolpress'); ?></h3><br/>
							<?php } else {  ?>
								<h3 class="wpsp-card-title"><?php esc_html_e( 'Enter Marks', 'wpschoolpress'); ?></h3>
							<?php } ?>
							<div class="">
								<form class="form-horizontal group-border-dashed" id="AddMarkForm" action="" style="border-radius: 0px;" method="post">
									<?php wp_nonce_field( 'MarksRegister', 'wps_marks_nonce', '', true ); ?>
                                    <input class="form-control" type="hidden" value="<?php echo esc_attr($subject_id);?>" name="SubjectID">
                                    <input class="form-control" type="hidden" value="<?php echo esc_attr(intval($class_id));?>" name="ClassID">
                                    <input class="form-control" type="hidden" value="<?php echo esc_attr(intval($exam_id));?>" name="ExamID">
									<table class="wpsp-table" cellspacing="0" width="100%" style="width: 100%;">
										<thead>
											<tr>
												<th><?php esc_html_e( 'RollNo.', 'wpschoolpress'); ?></th>
												<th><?php esc_html_e( 'Name', 'wpschoolpress' ); ?></th>
												<?php if((!isset($settings_data['markstype'])) || ($settings_data['markstype'] == "Number")) { ?>
													<th><?php esc_html_e( 'Marks', 'wpschoolpress' );?></th>
												<?php }else {?>
													<th><?php esc_html_e( 'Grade', 'wpschoolpress' );?></th>
												<?php } ?>
												<th><?php esc_html_e( 'Remarks', 'wpschoolpress');?></th>
												<?php if(!empty($extra_fields)){
													foreach($extra_fields as $extf){
													?>
													<th><?php echo esc_html($extf->field_text);?></th>
												<?php } } ?>
											</tr>
										</thead>
										<tbody>
											<?php
											if($mark_entered==1){
												$stable     =   $wpdb->prefix."wpsp_student";
												$sno        =   1;
												$stl = [];
												$studentlists   =   $wpdb->get_results("select class_id, sid from $stable");
												echo "<br />";
												foreach ($studentlists as $stu) {
                                            		if(is_numeric($stu->class_id) ){
                                                    	if($stu->class_id == $class_id){
                                                        	$stl[] = $stu->sid;
                                                		}
                                            		}else{
                                            			$class_id_array = unserialize( $stu->class_id );
														if(is_array($class_id_array)){
												 			if(in_array($class_id, $class_id_array)){
											 					$stl[] = $stu->sid;
															}
														}
													}
												}
												if (empty($stl)) {
													echo "<tr><td>".esc_html( 'No Students to retrive', 'wpschoolpress')."</td></tr>";
												}else {
													foreach ($stl as $id ) {
														$getslist = $wpdb->get_results($wpdb->prepare("SELECT * FROM $stable WHERE sid = %d order by CAST('s_rollno' as SIGNED)",$id));
                            							foreach ($getslist as $student ) {
                            								$usid       =   intval($student->wp_usr_id);
						    								$stroll     =   $student->s_rollno;
															$stfullname =   $student->s_fname.' '.$student->s_mname.' '.$student->s_lname;
                    										$marktable  =   $wpdb->prefix."wpsp_mark";
															$getmark = $wpdb->get_row($wpdb->prepare("SELECT * FROM $marktable WHERE class_id = %d AND student_id = %d AND subject_id = %d AND exam_id = %d",$class_id,$usid,$subject_id,$exam_id));
															$getmarkid      =   isset( $getmark->mid ) ? $getmark->mid : '';
															if( empty($getmark) ) {
																$mark_data  =   array( 'subject_id'=>$subject_id,'class_id'=>$class_id,'student_id'=>$usid,'exam_id'=>$exam_id );
																$m_ins      =   $wpdb->insert($marktable,$mark_data);
																if( $wpdb->insert_id )
																$getmarkid = $wpdb->insert_id;
															}
															?>
															<tr>
																<td class="number"><?php echo esc_html($stroll);?></td>
																<td class="number"><?php echo esc_html($stfullname);?></td>
																<td class="sch_mark">
																	<?php  if((!isset($settings_data['markstype'])) || ( $settings_data['markstype'] == "Number")){
									        							$classvar = "class='numbers wpsp-form-control'";
																	}else{
																		$classvar = "class='textboxvalue wpsp-form-control'";
																	}
																	?>
																	<input type="text" <?php  echo wp_kses_post($classvar);?> id="v_marks" value="<?php echo ((isset($getmark->mark)? esc_attr($getmark->mark) : ''));  ?>" name="marks[<?php echo esc_attr($getmarkid);?>][]">
																</td>
																<td class="sch_remarks">
																	<input type="text" class="textcls wpsp-form-control"  value="<?php echo esc_attr($getmark->remarks);  ?>" name="remarks[<?php echo esc_attr($getmarkid);?>][]">
																</td>
																<?php if(!empty($extra_fields)){
																	foreach($extra_fields as $extf){
																	?>
																	<td><input type="text" class="numbers wpsp-form-control" id="v_exmark" min="0" name="exmarks[<?php echo esc_attr($usid);?>][<?php echo esc_html($extf->field_id);?>]" value="<?php echo ((isset($extra_marks[$usid][$extf->field_id]) ? esc_attr($extra_marks[$usid][$extf->field_id]) : ''));?>"></td>
																<?php } } ?>
															</tr>
                                							<?php
                                							$sno++;
                                						}	
                                 					}
                                					echo "<input type='hidden' name='update' value='true'>";
                                				}
											}else{
												$stable     =   $wpdb->prefix."wpsp_student";
												$sno        =   1;
												$stl = [];
												$studentlists   =   $wpdb->get_results("select class_id, sid from $stable");
												echo "<br />";
												foreach ($studentlists as $stu) {
													if(is_numeric($stu->class_id) ){
														if($stu->class_id == $class_id){
										 					$stl[] = $stu->sid;
														}
													}else{
						 								$class_id_array = unserialize( $stu->class_id );
														if(is_array($class_id_array)){
								 							if(in_array($class_id, $class_id_array)){
								 								$stl[] = $stu->sid;
													 		}
														}
													}
												}
												if (empty($stl)) {
													echo "<tr><td>".esc_html( 'No Students to retrive3', 'wpschoolpress')."</td></tr>";
												}else {
													foreach ($stl as $id ) {
                                        				$id = esc_sql($id);
														$getslist = $wpdb->get_results($wpdb->prepare("SELECT * FROM $stable WHERE sid = %d order by CAST('s_rollno' as SIGNED)",$id));
														foreach( $getslist as $slist ) {
															?>
															<tr>
																<td class="number"><?php echo esc_html($slist->s_rollno);?></td>
																<td class="number"><?php echo esc_html($slist->s_fname.' '.$slist->s_mname.' '.$slist->s_lname);?></td>
																<td class="sch_mark">
																	<?php  if((!isset($settings_data['markstype'])) || ( $settings_data['markstype'] == "Number")){
                                                          				$classvar = "class='numbers markbox wpsp-form-control'";
																	}else {
                                                            			$classvar = "class='textboxvalue markbox wpsp-form-control'";
																	}
																	?>
																	<input type="text" <?php  echo  wp_kses_post($classvar);?> min="0" value="" name="marks[<?php echo esc_attr($slist->wp_usr_id);?>][]">
																</td>
																<td class="sch_remarks">
																	<input type="text" class="textcls wpsp-form-control"  value="" name="remarks[<?php echo esc_attr($slist->wp_usr_id);?>][]">
																</td>
																<?php if(!empty($extra_fields)){
																	foreach($extra_fields as $extf){
																		?>
																		<td><input type="text" class="wpsp-form-control" id="v_exmark1" min="0" name="exmarks[<?php echo esc_attr($slist->wp_usr_id);?>][<?php echo esc_html($extf->field_id);?>]"></td>
																		</td>
																	<?php }
															 	} ?>
															</tr>
														<?php
														$sno++;
														}
													}
												}
												if(empty($stl) && $mark_entered=='0'){
					 								echo "<tr><td>".esc_html( 'No Students to retrive', 'wpschoolpress')."</td></tr>";
												}else {  }
											} ?>
										</tbody>
									</table>
									<div class="wpsp-row">
										<div class="wpsp-col-md-12">
											<input  type="submit" class="wpsp-btn wpsp-btn-success" id="AddMark_Submit" name="AddMark_Submit"  value="Save Marks">
										</div>
									</div>
								</form>
							</div>
						</div>
						<?php
						}else if(isset($_POST['MarkAction']) && sanitize_text_field($_POST['MarkAction'])=='View Marks'){
							include_once( WPSP_PLUGIN_PATH .'/includes/wpsp-viewMark.php');
						}else{
							do_action( 'wpsp_marks_actions' );
						}
					} ?>
				</div>
			</div>
			<?php
			wpsp_body_end();
			wpsp_footer();

	/* ============================================================
	   PARENT ROLE
	   ============================================================ */
	} else if( $current_user_role == 'parent' ) {
		wpsp_topbar();
		wpsp_sidebar();
		wpsp_body_start();

		$parent_id     = intval( $current_user->ID );
		$student_table = $wpdb->prefix . "wpsp_student";
		$class_table   = $wpdb->prefix . "wpsp_class";
		$exam_table_name = $wpdb->prefix . 'wpsp_exam';

		/*
		 * Read URL params: sid = student wp_usr_id (base64), cid = class id (base64)
		 * These are set by the sidebar links for each child+course.
		 */
		/*
		 * The sidebar passes sid = student plugin row `sid` (base64),
		 * and cid = class id (base64).
		 */
		$url_sid      = isset( $_GET['sid'] ) ? intval( base64_decode( sanitize_text_field( $_GET['sid'] ) ) ) : 0;
		$url_class_id = isset( $_GET['cid'] ) ? intval( base64_decode( sanitize_text_field( $_GET['cid'] ) ) ) : 0;

		/*
		 * SINGLE VIEW: a specific student + class was clicked in the sidebar.
		 */
		if ( $url_sid > 0 && $url_class_id > 0 ) :

			/*
			 * Match on `sid` (plugin student row ID) and verify parent ownership.
			 * Also fetch wp_usr_id — that is what the marks table stores.
			 */
			$student_row = $wpdb->get_row( $wpdb->prepare(
				"SELECT sid, wp_usr_id, CONCAT_WS(' ', s_fname, s_mname, s_lname) AS full_name
				 FROM $student_table
				 WHERE sid = %d AND parent_wp_usr_id = %d",
				$url_sid,
				$parent_id
			) );

			/* wp_usr_id is what the AJAX marks handler expects */
			$url_student_id = $student_row ? intval( $student_row->wp_usr_id ) : 0;

			$class_row = $wpdb->get_row( $wpdb->prepare(
				"SELECT c_name FROM $class_table WHERE cid = %d",
				$url_class_id
			) );

			$exams = $wpdb->get_results( $wpdb->prepare(
				"SELECT eid, e_name FROM $exam_table_name WHERE classid = %d",
				$url_class_id
			) );

			$result_div_id = 'marks-result-' . $url_student_id . '-' . $url_class_id;
			?>

			<div class="wpsp-card">
				<div class="wpsp-card-head">
					<h3 class="wpsp-card-title">
						<?php echo esc_html( apply_filters( 'wpsp_student_marks_heading_item', 'Students Marks' ), 'wpschoolpress' ); ?>
					</h3>
				</div>
				<div class="wpsp-card-body">

					<?php if ( ! $student_row || ! $url_student_id ) : ?>
						<p class="wpsp-text-red"><?php esc_html_e( 'Access denied.', 'wpschoolpress' ); ?></p>

					<?php elseif ( empty( $exams ) ) : ?>
						<p class="wpsp-text-red"><?php esc_html_e( 'No exams found for this class.', 'wpschoolpress' ); ?></p>

					<?php else : ?>
						<div class="wpsp-form-group" style="margin-bottom: 10px;">
							<label><strong><?php esc_html_e( 'Select Exam:', 'wpschoolpress' ); ?></strong></label>
							<select class="wpsp-form-control wpsp-exam-dropdown"
							        style="max-width: 300px;"
							        data-student="<?php echo esc_attr( $url_student_id ); ?>"
							        data-class="<?php echo esc_attr( $url_class_id ); ?>"
							        data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpsp_marks_nonce' ) ); ?>">
								<option value="">-- <?php esc_html_e( 'Select Exam', 'wpschoolpress' ); ?> --</option>
								<?php foreach ( $exams as $exam ) : ?>
									<option value="<?php echo esc_attr( $exam->eid ); ?>">
										<?php echo esc_html( $exam->e_name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="wpsp-marks-result" id="<?php echo esc_attr( $result_div_id ); ?>"></div>
					<?php endif; ?>

				</div>
			</div>

		<?php
		/*
		 * FALLBACK: no specific student/class in URL — this shouldn't normally
		 * happen with a proper sidebar, but keep a safe fallback just in case.
		 */
		else :

			$students = $wpdb->get_results( $wpdb->prepare(
				"SELECT st.wp_usr_id, st.class_id, st.sid,
				        CONCAT_WS(' ', st.s_fname, st.s_mname, st.s_lname) AS full_name,
				        cl.c_name
				 FROM $student_table st
				 LEFT JOIN $class_table cl ON cl.cid = st.class_id
				 WHERE st.parent_wp_usr_id = %d",
				$parent_id
			) );
			?>
			<div class="wpsp-card">
				<div class="wpsp-card-head">
					<h3 class="wpsp-card-title">
						<?php echo esc_html( apply_filters( 'wpsp_student_marks_heading_item', 'Students Marks' ), 'wpschoolpress' ); ?>
					</h3>
				</div>
				<div class="wpsp-card-body">
					<p><?php esc_html_e( 'Please select a student and course from the sidebar to view marks.', 'wpschoolpress' ); ?></p>
				</div>
			</div>

		<?php endif; ?>

		<?php
		wpsp_body_end();
		wpsp_footer();

	/* ============================================================
	   STUDENT ROLE
	   ============================================================ */
	} else if( $current_user_role == 'student' ) {
		wpsp_topbar();
		wpsp_sidebar();
		wpsp_body_start();

		$student_id = intval( $current_user->ID );
		$ciid       = sanitize_text_field( stripslashes( $_GET['cid'] ) );
		$class_id   = intval( base64_decode( $ciid ) );
		?>
		<div class="wpsp-card">
			<div class="wpsp-card-head">
				<h3 class="wpsp-card-title"><?php esc_html_e( 'Your Marks', 'wpschoolpress' ); ?></h3>
			</div>
			<div class="wpsp-card-body">
				<div class="gap-top-bottom">
					<?php wpsp_MarkReport( $student_id, $class_id ); ?>
				</div>
			</div>
		</div>
		<?php
		wpsp_body_end();
		wpsp_footer();
	}

} else {
	// Include Login Section
	include_once( WPSP_PLUGIN_PATH . '/includes/wpsp-login.php' );
}
?>
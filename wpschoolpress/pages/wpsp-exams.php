<?php
if (!defined( 'ABSPATH' ) )exit('No Such File');
wpsp_header();
if( is_user_logged_in() ) {
    global $current_user, $wpdb;
	$current_user_role	=	$current_user->roles[0];
	$current_user_Id	=	intval($current_user->ID);
	$subject_table		=	$wpdb->prefix."wpsp_subject";
	if($current_user_role=='administrator' || $current_user_role=='teacher'){
		wpsp_topbar();
		wpsp_sidebar();
		wpsp_body_start();
		$filename	=	'';
		$header ='Exams';
		if( isset($_GET['tab'] ) && sanitize_text_field($_GET['tab']) == 'addexam' ) {
			if($current_user_role=='administrator'){
				$header	=	$label	=	__( 'Add New Exam', 'wpschoolpress');
				$filename	=	WPSP_PLUGIN_PATH .'includes/wpsp-examForm.php';
			}
		}else if(( isset($_GET['id']) && intval($_GET['id'])))  {
			if($current_user_role=='administrator'){
				$header	=	$label	=	__( 'Update Exam', 'wpschoolpress');
				$filename	=	WPSP_PLUGIN_PATH .'includes/wpsp-examForm.php';
			}
		}
		$extable	=	$wpdb->prefix."wpsp_exam";
		$ctable		=	$wpdb->prefix."wpsp_class";
		$wpsp_exams =	$wpdb->get_results( "select * from $extable");
		$class_ID	=	0;
		if( $current_user_role=='teacher' ) {
			$cuserId	=	intval($current_user->ID);
			/*$class_ID	=	$wpdb->get_results("SELECT DISTINCT c.cid,c.c_name,s.id FROM wp_wpsp_class c
								INNER JOIN wp_wpsp_subject s ON s.class_id= c.cid
								WHERE s.sub_teach_id ='".esc_sql($cuserId)."' || c.teacher_id = '".esc_sql($cuserId)."'"); */
			$class_ID = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT c.cid,c.c_name,s.id FROM $ctable c 
			INNER JOIN $subject_table s ON s.class_id= c.cid WHERE s.sub_teach_id = %d || c.teacher_id = %d",$cuserId,$cuserId));				
			$j=0;
			foreach( $class_ID as $class_IDa ) {
				$clsid[] = esc_sql($class_IDa->cid);
				$subid[] = esc_sql($class_IDa->id);
			}
			$msg		=	'Please Ask Principal To Assign Class';
			if( !empty( intval($class_ID )) ) {
				$clsid = array_map('intval', $clsid); // ensure all class IDs are integers
				$subid = array_map('intval', $subid); // ensure all subject IDs are integers
				$cls_placeholders = implode(',', array_fill(0, count($clsid), '%d'));
				$sub_placeholders = implode(',', array_fill(0, count($subid), '%d'));
				$query = $wpdb->prepare("SELECT * FROM $extable WHERE classid IN ($cls_placeholders) AND subject_id IN ($sub_placeholders)",...$clsid,...$subid);
				$wpsp_exams = $wpdb->get_results($query);
			//	$wpsp_exams =	$wpdb->get_results( "select * from $extable where classid IN (".implode(',',$clsid).") and subject_id IN (".implode(',',$subid).")");
			}
		}
		if( !empty( $filename) ) {
			include_once ( $filename );
		} else {?>
		<div class="wpsp-card">
      		<div class="wpsp-card-body">
				<div class="subject-head">
          			<?php if( $current_user_role=='teacher' && empty( $class_ID ) ) {
            			echo '<div class="alert alert-danger col-lg-2">'.esc_html($msg).'</div>';
          			} ?>
				</div>
				<table id="exam_class_table" class="wpsp-table" cellspacing="0" width="100%" style="width:100%">
					<thead>
						<tr>
							<th class="nosort">#</th>
							<th><?php esc_html_e( 'Exam Name', 'wpschoolpress' );?></th>
							<th><?php esc_html_e( 'Class Name', 'wpschoolpress' );?></th>
							<th><?php esc_html_e( 'Subject Name', 'wpschoolpress' );?></th>
							<th><?php esc_html_e( 'Start Date', 'wpschoolpress' );?></th>
							<th><?php esc_html_e( 'End Date', 'wpschoolpress' );?></th>
							<?php if($current_user_role=='administrator'){?>
								<th class="nosort"><?php esc_html_e( 'Action', 'wpschoolpress' );?></th>
							<?php }?>
						</tr>
					</thead>
					<tbody>
						<?php
						$sno=1;
						foreach( $wpsp_exams as $wpsp_exam ) {
							$classname = $sublist	=	'';
							$classid	=	isset($wpsp_exam->classid)  ? intval($wpsp_exam->classid) : '';
							if( !empty( intval($classid )) ) {
								$classname = $wpdb->get_var($wpdb->prepare("SELECT `c_name` FROM $ctable WHERE cid = %d",$classid));
							}
							$sublist	=	'-';
							if( !empty($wpsp_exam->subject_id) ) {
								$subject_list	=	array();
								$slist	=	str_replace( 'All,', '',$wpsp_exam->subject_id);
								if( !empty( $slist ) ) {
									$subject_ids = explode(',', $slist); // "1,2,3" -> [1,2,3]
									$subject_ids = array_map('intval', $subject_ids);
									$placeholders = implode(',', array_fill(0, count($subject_ids), '%d'));
									$query = $wpdb->prepare("SELECT sub_name FROM $subject_table WHERE id IN ($placeholders)",...$subject_ids);
									$subjectlist = $wpdb->get_results($query, ARRAY_A);		
								//	$subjectlist	=	$wpdb->get_results("SELECT sub_name FROM $subject_table WHERE id IN($slist) ", ARRAY_A );
									foreach( $subjectlist as $list ) {
										$subject_list[]	= $list['sub_name'];
									}
									$sublist	=	implode(", ",$subject_list);
								}
							}
							?>
							<tr id="<?php echo esc_attr($wpsp_exam->eid);?>">
								<td><?php echo  esc_html($sno); ?>
								<td><?php echo  esc_html($wpsp_exam->e_name);?></td>
								<td><?php echo  esc_html($classname); ?></td>
								<td><?php echo  esc_html($sublist); ?></td>
								<td><?php echo  esc_html(wpsp_ViewDate($wpsp_exam->e_s_date),'wpschoolpress'); ?></td>
								<td><?php echo  esc_html(wpsp_ViewDate($wpsp_exam->e_e_date),'wpschoolpress');?></td>
								<?php if($current_user_role=='administrator'){?>
									<td align="center">
										<div class="wpsp-action-col">
											<a href="<?php echo esc_url(wpsp_admin_url().'sch-exams&id='.intval($wpsp_exam->eid).'&edit=true');?>" title="Edit"><i class="icon dashicons dashicons-edit wpsp-edit-icon"></i></a>
											<a href="javascript:;" id="d_teacher" class="wpsp-popclick" data-pop="DeleteModal" title="Delete" data-id="<?php echo esc_attr($wpsp_exam->eid);?>" >
                      							<i class="icon dashicons dashicons-trash wpsp-delete-icon" data-id="<?php echo esc_attr($wpsp_exam->eid);?>"></i>
                    						</a>
										</div>
									</td>
								<?php } ?>
							</tr>
							<?php
								$sno++;
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<th class="nosort">#</th>
								<th><?php esc_html_e( 'Exam Name', 'wpschoolpress' );?></th>
								<th><?php esc_html_e( 'Class Name', 'wpschoolpress' );?></th>
								<th><?php esc_html_e( 'Subject Name', 'wpschoolpress' );?></th>
								<th><?php esc_html_e( 'Start Date', 'wpschoolpress' );?></th>
								<th><?php esc_html_e( 'End Date', 'wpschoolpress' );?></th>
								<?php if($current_user_role=='administrator'){?>
									<th class="nosort"><?php esc_html_e( 'Action', 'wpschoolpress' );?></th>
								<?php } ?>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		<?php } 
		if($current_user_role=='administrator'){?>
		<!--Info Modal-->
			<div class="modal fade" id="InfoModal" tabindex="-1" role="dialog" aria-labelledby="InfoModal" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="col-md-12">
							<div class="box box-success">
								<div class="box-header">
									<p class="box-title" id="InfoModalTitle"></p>
								</div><!-- /.box-header -->
								<div id="InfoModalBody" class="box-body PTZero">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div><!-- /.modal -->
			<?php
		}
  		wpsp_body_end();
  		wpsp_footer();
	}else if( $current_user_role=='parent' || $current_user_role='student'){
  		wpsp_topbar();
  		wpsp_sidebar();
  		wpsp_body_start();
  	?>
  	<div class="wpsp-card">
    	<div class="wpsp-card-head">
      		<h3 class="wpsp-card-title"><?php esc_html_e( 'Time Table', 'wpschoolpress' )?> </h3>
    	</div>
    	<div class="wpsp-card-body">
      		<table id="exam_class_table" class="wpsp-table" cellspacing="0" width="100%" style="width:100%">
        		<thead>
          			<tr>
            			<th class="nosort">#</th>
						<th><?php esc_html_e( 'Exam Name', 'wpschoolpress' );?></th>
						<th><?php esc_html_e( 'Class Name', 'wpschoolpress' );?></th>
						<th><?php esc_html_e( 'Subject Name', 'wpschoolpress' );?></th>
						<th><?php esc_html_e( 'Start Date', 'wpschoolpress' );?></th>
						<th><?php esc_html_e( 'End Date', 'wpschoolpress' );?></th>
					</tr>
				</thead>
        		<tbody>
          			<?php
        			//   $id = sanitize_text_field(stripslashes($_GET['cid']));
          			$class_id = base64_decode(sanitize_text_field($_GET['cid']));
          			$extable	=	$wpdb->prefix."wpsp_exam";
					$studtable	=	$wpdb->prefix."wpsp_student";
					$classtable	=	$wpdb->prefix."wpsp_class";
          			$wpsp_exams = [];
					if( $current_user_role=='parent' ) {
						//   $wpsp_exams =$wpdb->get_results( "SELECT DISTINCT e.*,c.c_name FROM $studtable st, $extable e, $classtable c where st.parent_wp_usr_id='".esc_sql($current_user_Id)."' AND e.classid='".esc_sql($class_id)."' AND c.cid=".esc_sql($class_id)."");
			  			$wpsp_exams = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT e.*, c.c_name FROM $studtable st, $extable e, $classtable c 
						WHERE st.parent_wp_usr_id = %d AND e.classid = %d AND c.cid = %d",$current_user_Id,$class_id,$class_id));
					} else {
            			//  $wpsp_exams =$wpdb->get_results( "SELECT DISTINCT e.*,c.c_name FROM $studtable st, $extable e, $classtable c where 
						//  st.wp_usr_id='".esc_sql($current_user_Id)."' AND e.classid='".esc_sql($class_id)."' AND c.cid='".esc_sql($class_id)."'");
			   			$wpsp_exams = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT e.*, c.c_name FROM $studtable st, $extable e, $classtable c 
						WHERE st.wp_usr_id = %d AND e.classid = %d AND c.cid = %d",$current_user_Id,$class_id,$class_id));
					}
          			$sno=1;
          			if(!empty($wpsp_exams)){
  						foreach ($wpsp_exams as $wpsp_exam){
              				$sublist	=	'';
              				if( !empty($wpsp_exam->subject_id) ) {
                				$subject_list	=	array();
								$subject_ids = explode(',', $wpsp_exam->subject_id); // "1,2,3" -> [1,2,3]
								$subject_ids = array_map('intval', $subject_ids);
								$placeholders = implode(',', array_fill(0, count($subject_ids), '%d'));
  								//	$subjectlist	=	$wpdb->get_results("SELECT sub_name FROM $subject_table WHERE id IN($wpsp_exam->subject_id)", ARRAY_A );
								$query = $wpdb->prepare("SELECT sub_name FROM $subject_table WHERE id IN ($placeholders)",...$subject_ids);
								$subjectlist = $wpdb->get_results($query, ARRAY_A);				
  								foreach( $subjectlist as $list ) {
  									$subject_list[]	= $list['sub_name'];
  								}
  								$sublist	=	implode(", ",$subject_list);
  							}
  							?>
  							<tr id="<?php echo esc_attr($wpsp_exam->eid);?>" class="pointer">
  								<td><?php echo esc_html($sno);?></td>
  								<td><?php echo esc_html($wpsp_exam->e_name);?></td>
  								<td><?php echo esc_html($wpsp_exam->c_name);?> </td>
  								<td style="width: 580px;"><?php echo esc_html($sublist); ?> </td>
  								<td><?php echo esc_html(wpsp_ViewDate($wpsp_exam->e_s_date),'wpschoolpress'); ?></td>
  								<td><?php echo esc_html(wpsp_ViewDate($wpsp_exam->e_e_date),'wpschoolpress');?></td>
  							</tr>
  							<?php
  							$sno++;
  						}
          			}
          			?>
        		</tbody>
      		</table>
    	</div>
  	</div>
  	<?php
  	wpsp_body_end();
  	wpsp_footer();
	}
}else{
	//Include Login Section
  include_once( WPSP_PLUGIN_PATH .'/includes/wpsp-login.php');
}
?>
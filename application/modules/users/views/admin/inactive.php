<h3>Inactive users</h3>

<p class="float-right">
	[ <a href="<?=site_url('admin/users/index');?>">Active users</a> ]
</p>

<?=form_open('admin/users/action'); ?>
<table border="0" class="listTable clear-both">
    
  <thead>
	<tr>
		<th class="first"><div></div></th>
		<th><a href="#">Name</a></th>
		<th><a href="#">E-mail</a></th>
		<th><a href="#">Role</a></th>
		<th><a href="#">Joined</a></th>
		<th><a href="#">Last visit</a></th>
		<th class="last"><span>Actions</span></th>
	</tr>
  </thead>
  <tfoot>
  	<tr>
  		<td colspan="7">
  			<div class="inner"><? $this->load->view('admin/layout_fragments/pagination'); ?></div>
  		</td>
  	</tr>
  </tfoot>
  
  	<tbody>
	<? if (!empty($users)): ?>
		<? foreach ($users as $member): ?>
			<tr>
				<td align="center"><input type="checkbox" name="selected[]" value="<?= $member->id; ?>" /></td>
				<td><?=$member->full_name; ?></td>
				<td><?=anchor('admin/users/edit/' . $member->id, $member->email); ?></td>
				<td><?=$member->role; ?></td>
				<td><?=date('M d, Y', $member->created_on); ?></td>
				<td><?=($member->last_login > 0 ? date('M d, Y', $member->last_login) : 'Never'); ?></td>
				<td>
					<?= anchor('admin/users/activate/' . $member->id, 'Activate') . ' | ' .
						anchor('admin/users/delete/' . $member->id, 'Delete', array('class'=>'confirm')); ?>
				</td>
			  </tr>
		<? endforeach; ?>
	<? else: ?>
		<tr><td colspan="7">There are no inactive users.</td></tr>
	<? endif; ?>
	</tbody>

</table>

<? $this->load->view('admin/layout_fragments/table_buttons', array('buttons' => array('activate', 'delete') )); ?>

<?=form_close(); ?>
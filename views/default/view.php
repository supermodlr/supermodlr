<?php
//@todo convert this whole page to be generic "list" template and convert the whole thing to use angular js
?>
<div>
<h1><?php echo ucfirst(str_replace('_',' ',$model_name)); ?></h1>
	<a href="/supermodlrui/<?php echo $model_name; ?>/create">Create</a><?php
	$js_template = '';
	if (count($model_rows) > 0)
	{
		?><table><?php
		$c = 0;
		
		foreach ($model_rows as $row)
		{
			if ($c == 0)
			{ ?><thead><tr><?php
				$col_count = 0;
				foreach ($fields as $col => $val) 
				{
					if ($col_count == 10) continue;
					?><td><?=$col ?>
<div><input type="text" id="filter__<?=$col ?>" style='width: 80px' class="filter_input"/></div>
					</td><?php
					if ($col == '_id') 
					{
						$js_template .= '<td><a href="/supermodlrui/'.$model_name.'/read/\'+row._id+\'">\'+row._id+\'</a></td>';
					}
					else
					{
						$js_template .= '<td>\'+row.'.$col.'+\'</td>';
					}					
					
					$col_count++;
				} 

				$js_template .= '<td><a href="/supermodlrui/'.$model_name.'/update/\'+row._id+\'">Edit</a></td><td><a href="/supermodlr/'.$model_name.'/delete/\'+row._id+\'">Delete</a></td>';
				?></tr></thead><tbody id="data_body"><?php 
			}
			$col_count = 0;
			?><tr><?php 
			foreach ($fields as $col => $val) 
			{
				if ($col_count == 10) continue;
				if ($col == '_id') 
				{
					?><td><a href="/supermodlrui/<?php echo $model_name; ?>/read/<?=$row['_id']; ?>"><?=$row['_id']; ?></a></td><?php
				}
				else
				{
					?><td><?php if (isset($row[$col]) && is_scalar($row[$col])) { echo $row[$col]; } else if (isset($row[$col])) { echo substr(var_export($row[$col],TRUE),0,25); } ?></td><?php
				}
				$col_count++;
			} ?>

				
				<td><a href="/supermodlrui/<?php echo $model_name; ?>/update/<?php echo $row['_id']; ?>">Edit</a></td>
				<td><a href="/supermodlrui/<?php echo $model_name; ?>/delete/<?php echo $row['_id']; ?>">Delete</a></td>
			</tr>
			<?php
			$c++;
		}
		?></tbody></table><?php
	}
	else 
	{
		?><br/>No results<?php
	}
?></div>

<script type="text/javascript">

function filter(o) {

	//get all filter data
	var query = {
		"from": "<?=$model->get_name() ?>",
		"where": {},
		"limit": 20,
	};
	$('.filter_input').each(function() {
		var field_key = this.id.split('__')[1];
		if (this.value != '') {
			query.where[field_key] = {"$regex": "/^"+this.value+"/i"};
		}
	});

	$.ajax({
		"url": '<?=$controller->api_path()?><?=$model->get_name() ?>/query/?q='+JSON.stringify(query)+'&d='+(new Date()).valueOf(),
	}).done(function(result){
console.log(result);
		$('#data_body').empty();
		for (var i = 0; i < result.length; i++) {
			var row = result[i];
			$('#data_body').append('<tr><?=$js_template ?></tr>');
		}


	});
}

$('.filter_input').bind({
	keypress: function() {
	filter(this);
	},
	change: function() {
	filter(this);
	}
});


</script>
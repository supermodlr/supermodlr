<ul><?php
	foreach ($models as $model)
	{
		?><li><a href="/supermodlrui/<?php echo $model['model_name']; ?>"><?php echo ucfirst(str_replace('_',' ',$model['model_name'])); ?></a></li><?php
	}
?></ul>
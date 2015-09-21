<script>
	$(document).ready(function(e){
		$('.has_tooltip').tooltip();
		
		function split( val ) {
	      return val.split( /,\s*/ );
	    }
	    function extractLast( term ) {
	      return split( term ).pop();
	    }
		$('#tag-string')
	      // don't navigate away from the field on tab when selecting an item
	      .bind('keydown', function( event ) {
	        if ( event.keyCode === $.ui.keyCode.TAB &&
	            $( this ).autocomplete( "instance" ).menu.active ) {
	          event.preventDefault();
	        }
	      })
      .autocomplete({
        source: function( request, response ) {
          $.getJSON( '/tags/list_tags', {
            query: extractLast( request.term )
          }, response );
        },
        search: function() {
          // custom minLength
          var term = extractLast( this.value );
          if ( term.length < 2 ) {
            return false;
          }
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
      });
	});
</script>
<?php $this->assign('title', 'Uploaded files'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('List Tags'), ['controller' => 'Tags', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Tag'), ['controller' => 'Tags', 'action' => 'add']) ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData); ?>
</div>
<div class="uploadedfiles form large-10 medium-9 columns">
    <?= $this->Form->create($uploadedfile, ['type' => 'file']) ?>
    <fieldset>
        <legend><?= __('Upload file to shared drive') ?></legend>
        <?php
            echo $this->Form->file('uploaded_file');
            echo $this->Form->input('tag_string', 
				['type' => 'text',
				'label' => 'Tags for this file (enter as comma separated list)',
				'title' => 'List tags for this file, separating each tag with a comma, e.g. tag1, tag2, tag3',
				'class' => 'has_tooltip']);
			if ($userData['is_admin']) {
				echo $this->Form->input('private',
					['label' => 'Mark this file private (accessible only by RRAN members)?',
					'title' => 'Non-private files can be accessed by members of the public on the web',
					'checked' => true,
					'class' => 'has_tooltip']);
			}
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>

<ul id="form-elements" style="display: none">

      {#form}
      <li class="form-element" id="form-settings-element" data-type="form-settings">
        <h2 id="form-title-label">{title}</h2>
        <p id="form-description-label">{description}</p>
      </li>

      <li>
	  
        <ul id="sortable-elements" class="ui-widget-header">
		
		<div id="sortable-elements_drag_drop" class="" style="width:91%; height:50px; text-align:center; margin:10px 0 10px 25px; border:2px dashed #00FFFF; padding:10px;">
		  Drop me here
		</div> 
          {#fields}

            <!-- Single Line Text -->

            {@eq key="{type}" value="element-single-line-text"} 


              <li class="form-element {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-type="element-single-line-text">
                <label>
                  <span class="label-title">{title}</span>
                  {#required}<span class="required-star"> *</span>{/required}
                </label>
                <input type="text" class="form-control" value="" disabled />
				<p class="description">{description}</p>
              </li>

            {/eq}


            <!-- Number -->

            {@eq key="{type}" value="element-number"} 

              <li class="form-element {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-type="element-number">
                <label>
                  <span class="label-title">{title}</span>
                  {#required}<span class="required-star"> *</span>{/required}
                </label>
                <input type="number" class="form-control" value="" disabled />
              </li>

            {/eq}

            <!-- Paragraph Text -->

            {@eq key="{type}" value="element-paragraph-text"} 

            <li class="form-element {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-type="element-paragraph-text">
              <label>
                <span class="label-title">{title}</span>
                {#required}<span class="required-star"> *</span>{/required}
              </label>
              <textarea disabled></textarea>
            </li>

            {/eq}

            <!-- Multiple-Choice -->

            {@eq key="{type}" value="element-multiple-choice"}

              <li class="form-element {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-type="element-multiple-choice">

                <label>
                  <span class="label-title">{title}</span>
                  {#required}<span class="required-star"> *</span>{/required}
                </label>

                <div class="choices" data-type="settings-choice-radio">

                  {#choices}

                    <div class="choice radio disabled">
                      <label>
                        <input type="radio" class="option-{@idx}{.}{/idx}" name="element-{position}-choice" value="{value}" {#checked}checked{/checked} disabled>
                        <span class="choice-label">{title}</span>
                      </label>
                    </div>

                  {/choices}

                </div>

              </li>

            {/eq}

            <!-- Dropdown -->

            {@eq key="{type}" value="element-dropdown"}

              <li class="form-element {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-type="element-dropdown">
                <label>
                  <span class="label-title">{title}</span>
                  {#required}<span class="required-star"> *</span>{/required}
                </label>
                <select style="width: 50%" class="choices" data-type="settings-dropdown" disabled>

                  {#choices}

                    <option class="option-{@idx}{.}{/idx}" value="{value}" {#checked}selected{/checked}><span class="choice-label">{title}</span></option>

                  {/choices}

                </select>
              </li>

            {/eq}

            <!-- Checkboxes -->

            {@eq key="{type}" value="element-checkboxes"}

              <li class="form-element {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-type="element-checkboxes">

                <label>
                  <span class="label-title">{title}</span>
                  {#required}<span class="required-star"> *</span>{/required}
                </label>

                <div class="choices" data-type="settings-choice-checkbox">

                  {#choices}

                  <div class="choice checkbox disabled">
                    <label>
                      <input type="checkbox" class="option-{@idx}{.}{/idx}" {#checked}checked{/checked} name="element-{position}-choice" value="{value}" disabled>
                      <span class="choice-label">{title}</span>
                    </label>
                  </div>

                  {/choices}
                  
                </div>

              </li>

            {/eq}

            <!-- Section Break -->

            {@eq key="{type}" value="element-section-break"}

              <li class="form-element section-break {#required}required{/required}" id="element-{position}" data-label="{title}" data-position="{position}" data-description="{description}" data-type="element-section-break">
                <hr/>
                <label class="label-title"><span class="label-title">{title}</label>
                <p class="description">{description}</p>
              </li>

            {/eq}

          {/fields}

        </ul>

      </li>

      {/form}

    </ul>
<script>
/* com last 
   $( ".field-button" ).draggable({ connectToSortable: "#sortable-elements_drag_drop",helper: "clone",revert: "invalid",stop: function (event, ui) {
         var thisdatatype = ($(this).attr('data-type'));
		 $( "."+thisdatatype ).trigger( "click" );
		 $( "#sortable-elements_drag_drop" ).find( "p" ).html( "Doner" );
     },start: function (event, ui) {
        $( "#sortable-elements_drag_drop" ).find( "p" ).html( "Drop me here" ); 
     }
   });
   <div id="sortable-elements_drag_drop" class="" style="width:300px; height:50px; text-align:center; margin-bottom:50px; border:2px dashed #00FFFF;">
   */
   $( ".field-button" ).draggable({ connectToSortable: "#sortable-elements_drag_drop",helper: "clone",revert: "invalid",stop: function (event, ui) {
         $("#sortable-elements_drag_drop").animate({'border-width' : '2px','border-color' : '#00FFFF'}, 500);
     },start: function (event, ui) {
		$( "#sortable-elements_drag_drop" ).html( "Drop me here" ); 
		$("#sortable-elements_drag_drop").animate({'border-width':'2px','border-color' : 'red'}, 500);
     }
   });
   $( "#sortable-elements_drag_drop" ).droppable({
		revert: true,
		over : function(){
            $(this).animate({'border-width' : '2px','border-color' : 'green'}, 500);
        },
		out : function(){
            $(this).animate({'border-width' : '2px','border-color' : 'red'}, 500);
		},
	    drop: function( event, ui ) {
		 	 var thisdatatype = ($(ui.draggable).attr('data-type'));
		 	 $( "."+thisdatatype ).trigger( "click" );
	 		 $( this ).html( "Done !!!" );
	  	}
	});
</script>
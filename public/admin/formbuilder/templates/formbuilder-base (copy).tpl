	  <div class="col-sm-5 left-col" id="">
		<ul class="nav nav-tabs" role="tablist">
          <li class="active toolbox-tab" data-target="#add-field">Add a Field</li>
          <li class="toolbox-tab" data-target="#field-settings">&nbsp;&nbsp;Field Settings&nbsp;&nbsp;</li>
          <li class="toolbox-tab" data-target="#form-settings">Form Settings</li>
		  <li class="toolbox-tab pull-right" data-target="#form-review">Review of Systems</li>
          <!-- <li class="toolbox-tab" data-target="#rules">Rules</li>-->					 
        </ul>
        <div class="tab-content">
         <div class="tab-pane active" style="padding: 20px;" id="add-field">
			 <div class="sectiona">
<!-- <div id="draggable2" class="field-button ui-widget-content ui-draggable ui-draggable-handle" style="width:200px; margin-bottom:50px;">
<p>I revert when I'm not dropped</p>
</div> --> 

<!-- <div id="sortable-elementsaa" class="field-button" style="width:200px; margin-bottom:50px;">
  <p>Drop me here</p>
</div> -->

<a data-type="element-single-line-text" id="draggable2" class="field-button new-element element-single-line-text margin">Single Line Text</a>
<a data-type="element-paragraph-text" id="draggable2" class="field-button new-element element-paragraph-text margin">Paragraph</a>
<a data-type="element-checkboxes" class="field-button new-element element-checkboxes margin"> Checkboxes</a>
<a data-type="element-multiple-choice" class="field-button new-element element-multiple-choice margin"> Multiple Choice</a>
<a data-type="element-dropdown" class="field-button new-element element-dropdown">Dropdown</a>
<a data-type="element-number" class="field-button new-element element-number">Number</a>
<a data-type="element-section-break" class="field-button new-element element-section-break">Section Break</a>
<a data-type="element-email" class="field-button new-element element-email">Date</a>
<!--  <button class="button new-element" data-type="element-email" style="width: 100%;">Email</button> -->
    </div> 
            <div style="clear:both"></div>

          </div>

          <div class="tab-pane" id="field-settings" style="padding: 20px; display: none; margin: none;visibility:visible;">

            <div class="section">
				<div class="form-group">
					<div class="row">
						<label class="col-sm-3">Label:</label>
						<div class=" col-md-8">
							  <input type="text" class="form-control" id="field-label" value="Untitled test" />
						</div>
					</div>
				</div>
            </div>
			<div class="section" id="field-choices" style="display: none;">

              <div class="form-group">
                <label class="col-sm-3">Choices:</label>
              </div>

            </div>

            <div class="section" id="field-options"> 
				<div class="form-group">
					<div class="row">
						<label class="col-sm-3">Options:</label>
						<div class=" col-md-8">
							<label class=" i-checks"><input type="checkbox" id="required" class="form-control"><i></i>Required</label>
							
							
						</div>
					</div>
				</div>
				
            
              

            </div>

            <div class="section" id="field-description"> 
              <div class="form-group">
					<div class="row">
						<label class="col-sm-3">Description:</label>
						<div class=" col-md-8">
							<textarea id="description" placeholder = "Add a longer description to this field"></textarea>
						</div>
					</div>
				</div>
				
              

            </div>
				<div class="form-group">
					<div class="row">
						<label class="col-sm-3">&nbsp;</label>
						<div class=" col-md-8">
							 <button type="button" class="btn btn-default" id="control-remove-field">Remove</button>
						</div>
					</div>
				</div>
            <!-- <button class="button" id="control-add-field">Add Field</button> -->
          </div>

          <div class="tab-pane" id="form-settings" style="padding: 20px; display: none;visibility:visible;">

            <div class="section">
				<div class="form-group">
					<div class="row">
						<label class="col-sm-3">Title:</label>
						<div class="col-md-8" id="form-title_grp">
							 <input type="text" class="bind-control form-control" data-bind="#form-title-label" id="form-title" value="" />
							 <div class="help-block with-errors" id="form-title_err_msg"></div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label class="col-sm-3">Description:</label>
						<div class=" col-md-8">
							  <textarea class="bind-control form-control" data-bind="#form-description-label" id="form-description"></textarea>
						</div>
					</div>
				</div>
            </div>
          </div>
<div class="tab-pane" id="form-review" style="padding: 20px; display: none;visibility:visible;">

            <div class="section">
			<div id="setting_data">
			
			</div>
			 <!-- <form name="frm" style="font-size: 14px;">
				<div class="form-group">
					<label class="col-sm-9">Review of Settings</label>
						<div class="col-sm-3">
							<span style="float: right;"><input type="checkbox" id="select_all" onclick="CheckUncheckAll();" /> All</span>
						</div>
				</div>           
                 
                <ul class="mainMenu">					
					<li><input class="checkbox parent_menu"  name="constitutional" type="checkbox" /> Constitutional
					<span id="click_advance"><i class="fa fa-plus" aria-hidden="true"></i></span>
					   <ul class="sub_menu" id="constitutional_menu" style="display:none;">
					      <li><input class="checkbox" data-parent="constitutional" name="fever" type="checkbox"  /> Fever</li>
						  <li><input class="checkbox" data-parent="constitutional" name="chills" type="checkbox" /> Chills</li>
						  <li><input class="checkbox" data-parent="constitutional" name="body_ache" type="checkbox" /> Body Ache</li>
						  <li><input class="checkbox" data-parent="constitutional" name="appetite" type="checkbox" /> Change in Appetite</li>
						  <li><input class="checkbox" data-parent="constitutional" name="lethargic" type="checkbox" /> Lethargic</li>
						  <li><input class="checkbox" data-parent="constitutional" name="feeling_ill" type="checkbox" /> Feeling Ill</li>
						  <li><input class="checkbox" data-parent="constitutional" name="constitutional_other" type="checkbox" /> Other</li>
						  <li class="addNew"><input type="text" class="add_new" name="add_new" placeholder="add custom" />
						  <input type="button" name="add_field" value="Add" class="addButton btn btn-danger"  onclick="add_new_field('constitutional_menu','constitutional');"></li>
					   </ul>					
					</li>
				</ul>
				<div class="constitutional_info" style="display:none;">
				
				    <li class="form-element section-break customData" id="constitutional" data-label="Constitutional Information" data-description="" data-type="element-section-break"><hr><label class="label-title"><span class="label-title">Constitutional Information</span></label><p class="description">Constitutional Information</p></li>
					
					<li class="form-element customData fever" id="fever" data-label="Fever" data-type="element-custom-choice">
					    <label><span class="label-title">Fever</span></label>
						<input type="radio" name="fever" value="1" disabled />Yes
						<input type="radio" name="fever" value="0" disabled />No
						 <p class="description">add explanation</p> 
					</li>
					<li class="form-element customData chills" id="chills" data-label="Chills" data-type="element-custom-radio">
					    <label><span class="label-title">Chills</span></label>
						<input type="radio" name="chills" value="1" />Yes
						<input type="radio" name="chills" value="0" />No
						<p class="description">add explanation</p>
					</li>
					<li class="form-element customData body_ache" id="body_ache" data-label="Body Ache" data-type="element-custom-radio">
					    <label><span class="label-title">Body Ache</span></label>
						<input type="radio" name="body_ache" value="1" />Yes
						<input type="radio" name="body_ache" value="0" />No
						<p class="description">add explanation</p>
					</li>
					<li class="form-element customData appetite" id="appetite" data-label="Appetite" data-type="element-custom-radio">
					    <label><span class="label-title">Appetite</span></label>
						<input type="radio" name="appetite" value="1" />Yes
						<input type="radio" name="appetite" value="0" />No
						<p class="description">add explanation</p>
					</li>
					<li class="form-element customData lethargic" id="lethargic" data-label="Lethargic" data-type="element-custom-radio">
					    <label><span class="label-title">Lethargic</span></label>
						<input type="radio" name="lethargic" value="1" />Yes
						<input type="radio" name="lethargic" value="0" />No
						<p class="description">add explanation</p>
					</li>
					<li class="form-element customData feeling_ill" id="feeling_ill" data-label="Feeling Ill" data-type="element-custom-radio">
					    <label><span class="label-title">Feeling Ill</span></label>
						<input type="radio" name="feeling_ill" value="1" />Yes
						<input type="radio" name="feeling_ill" value="0" />No
						<p class="description">add explanation</p>
					</li>
					<li class="form-element customData constitutional_other" id="constitutional_other" data-label="Other" data-type="element-custom-radio">
					    <label><span class="label-title">Other</span></label>
						<input type="radio" name="constitutional_other" value="1" />Yes
						<input type="radio" name="constitutional_other" value="0" />No
						<p class="description">add explanation</p>
					</li>					
				
				</div>
				<div class="addData btn btn-danger"><a onclick="addCustomData();">Add</a></div>
			  </form> -->
            </div>

          </div>
        </div>

      </div>

      <div class="col-sm-7 right-col" id="form-col">

        <div class="loading">
          Loading...
        </div>

      </div>

      <div style="clear: both"></div>

    
  <div style="clear: both"></div>

<div style="clear: both"></div>

<script>
fun_get_setting_data();
</script>

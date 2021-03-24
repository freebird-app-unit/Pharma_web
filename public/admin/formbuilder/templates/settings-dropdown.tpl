{#choices}
<div class="radio choice form-group">
	
	<label class="i-checks">
		<input type="radio" name="choice" class="option" {#checked}checked{/checked}><i></i>&nbsp;
	</label>

	<input type="text" class="bind-control" data-bind=".{bindingClass}" style="display: inline-block; width: 65%;" value="{title}" />&nbsp;
	
	<button type="button" class="button btn-success add-choice">+</button>&nbsp;
	<button type="button" class="button btn-danger remove-choice" data-delete=".{bindingClass}">-</button>

</div>
{/choices}
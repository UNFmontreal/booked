function ResourceManagement(opts) {
	var options = opts;

	var elements = {
		activeId: $('#activeId'),

		imageDialog: $('#imageDialog'),
		deleteDialog: $('#deletePrompt'),
		statusDialog: $('#statusDialog'),
		durationDialog: $('#durationDialog'),
		capacityDialog: $('#capacityDialog'),
		accessDialog: $('#accessDialog'),

		imageForm: $('#imageForm'),
		removeImageForm: $('#removeImageForm'),
		defaultImageForm: $('#defaultImageForm'),
		deleteForm: $('#deleteForm'),
		durationForm: $('#durationForm'),
		capacityForm: $('#capacityForm'),
		accessForm: $('#accessForm'),
		statusForm: $('#statusForm'),

		statusReasons: ('.reasonId'),
		statusOptions: ('.statusId'),
		addStatusReason: ('.addStatusReason'),
		newStatusReason: ('.newStatusReason'),
		existingStatusReason: ('.existingStatusReason'),
		resourceStatusReason: ('.resourceStatusReason'),
		addStatusIcon: ('.addStatusIcon'),

		addForm: $('#addResourceForm'),
		statusOptionsFilter: $('#resourceStatusIdFilter'),
		statusReasonsFilter: $('#resourceReasonIdFilter'),
		filterTable: $('#filter-resources-panel'),
		filterButton: $('#filter'),
		clearFilterButton: $('#clearFilter'),

		bulkUpdatePromptButton: $('#bulkUpdatePromptButton'),
		bulkUpdateDialog: $('#bulkUpdateDialog'),
		bulkUpdateList: $('#bulkUpdateList'),
		bulkUpdateForm: $('#bulkUpdateForm'),
		bulkEditStatusOptions: $('#bulkEditStatusId'),
		bulkEditStatusReasons: $('#bulkEditStatusReasonId'),

		addResourceButtons: $('.add-resource'),
		addResourceDialog: $('#add-resource-dialog'),

		userSearch: $('#userSearch'),
		userDialog: $('#userDialog'),
		changeUserForm: $('#changeUserForm'),
		browseUserDialog: $('#allUsers'),
		browseUsersButton: $('#browseUsers'),
		resourceUserList: $('#resourceUserList'),
		allUsersList: $('#allUsersList'),

		groupSearch: $('#groupSearch'),
		groupDialog: $('#groupDialog'),
		browseGroupDialog: $('#allGroups'),
		browseGroupsButton: $('#browseGroups'),
		resourceGroupList: $('#resourceGroupList'),
		allGroupsList: $('#allGroupsList'),

		resourceGroupDialog: $('#resourceGroupDialog'),
		resourceGroupForm: $('#resourceGroupForm'),
		changeGroupForm: $('#changeGroupForm'),
		groupDiv: $('#resourceGroups'),
		autoAssign: $('#autoAssign'),
		removeAllPermissions: $('#autoAssignRemoveAllPermissions'),

		enableCheckIn: $('#enableCheckIn'),
		autoReleaseMinutes: $('#autoReleaseMinutes'),
		autoReleaseMinutesDiv: $('#autoReleaseMinutesDiv'),

		colorForm: $('#colorForm'),
		reservationColor: $('#reservationColor'),

		creditsDialog: $('#creditsDialog'),
		creditsForm: $('#creditsForm'),
		creditsPerSlot: $('#creditsPerSlot'),
		peakCreditsPerSlot: $('#peakCreditsPerSlot'),

		checkAllResources: $('#checkAllResources'),
		checkNoResources: $('#checkNoResources'),

		copyDialog: $('#copyDialog'),
		copyName: $('#copyResourceName'),
		copyForm: $('#copyForm'),

		importDialog: $('#importDialog'),
		importForm: $('#importForm'),
		importTrigger: $('#import-resources'),
		removeImageName: $('#removeImageName'),
		defaultImageName: $('#defaultImageName'),

		bulkDeletePromptButton: $('#bulkDeletePromptButton'),
		bulkDeleteDialog: $('#bulkDeleteDialog'),
		bulkDeleteList: $('#bulkDeleteList'),
		bulkDeleteForm: $('#bulkDeleteForm'),
		checkAllDeleteResources: $('#checkAllDeleteResources'),
		checkNoDeleteResources: $('#checkNoDeleteResources'),

		checkAllowConcurrent: $('#allowConcurrentChk'),
		maxConcurrent: $('#maxConcurrentReservations'),
		allowConcurrentDiv: $('#allowConcurrentDiv'),
	};

	var resources = {};
	var reasons = [];

	function initializeResourceUI(id, details) {
		// no-op
	}

	ResourceManagement.prototype.init = function () {
		$('.resourceDetails').each(function () {
			var indicator = $('.indicator');
			var details = $(this);
			var id = details.attr('data-resourceId');

			initializeResourceUI(id, details);

			details.find('.update').click(function (e) {
				e.preventDefault();
				setActiveResourceId(id);
			});

			details.find('.imageButton').click(function (e) {
				showChangeImage(e);
			});

			var subscriptionCallback = function (data) {
				details.find('.publicSettingsPlaceHolder').html(data);
			};

			details.find('.renameButton').click(function (e) {
				e.stopPropagation();
				details.find('.resourceName').editable('toggle');
			});

			details.find('.copyButton').click(function (e) {
				e.stopPropagation();
				elements.copyName.val(getActiveResource().name + ' ' + options.copyText);
				elements.copyDialog.modal('show');
				elements.copyName.select().focus();
			});

			details.find('.changeScheduleButton').click(function (e) {
				e.stopPropagation();
				details.find('.scheduleName').editable('toggle');
			});

			details.find('.changeResourceType').click(function (e) {
				e.stopPropagation();
				details.find('.resourceTypeName').editable('toggle');
			});

			details.find('.changeSortOrder').click(function (e) {
				e.stopPropagation();
				details.find('.sortOrderValue').editable('toggle');
			});

			details.find('.changeLocation').click(function (e) {
				e.stopPropagation();
				details.find('.locationValue').editable('toggle');
			});

			details.find('.changeContact').click(function (e) {
				e.stopPropagation();
				details.find('.contactValue').editable('toggle');
			});

			details.find('.changeDescription').click(function (e) {
				e.stopPropagation();
				details.find('.descriptionValue').editable('toggle');
			});

			details.find('.changeNotes').click(function (e) {
				e.stopPropagation();
				details.find('.notesValue').editable('toggle');
			});

			details.find('.changeResourceAdmin').click(function (e) {
				e.stopPropagation();
				details.find('.resourceAdminValue').editable('toggle');
			});

			details.find('.adminButton').click(function (e) {
				showResourceAdmin(e);
			});

			details.find('.deleteButton').click(function (e) {
				showDeletePrompt(e);
			});

			details.find('.changeAttribute').click(function (e) {
				e.stopPropagation();
				$(e.target).closest('.updateCustomAttribute').find('.inlineAttribute').editable('toggle');
			});

			details.find('.changeStatus').click(function (e) {
				showStatusPrompt(e);
			});

			details.find('.changeDuration').click(function (e) {
				showDurationPrompt(e);
			});

			details.find('.changeCapacity').click(function (e) {
				showCapacityPrompt(e);
			});

			details.find('.changeAccess').click(function (e) {
				showAccessPrompt(e);
			});

			details.find('.changeUserPermission').click(function (e) {
				changeUserPermissions();
				elements.userDialog.modal('show');
			});

			details.find('.changeGroupPermissions').click(function (e) {
				changeGroupPermissions();
				elements.groupDialog.modal('show');
			});

			details.find('.changeResourceGroups').click(function (e) {
				changeResourceGroups();
				elements.resourceGroupDialog.modal('show');
			});

			details.find('.resourceColorPicker').on('change', function (e) {
				setActiveResourceId(id);
				var color = $(this).val();
				elements.reservationColor.val(color);
				elements.colorForm.submit();
			});

			details.find('.clearColor').click(function (e) {
				$(this).siblings('.resourceColorPicker').val('#ffffff');
				elements.reservationColor.val('');
				elements.colorForm.submit();
			});

			details.find('.changeCredits').click(function (e) {
				var resource = getActiveResource();
				elements.creditsPerSlot.val(resource.credits);
				elements.peakCreditsPerSlot.val(resource.peakCredits);
				elements.creditsDialog.modal('show');
			});

			details.delegate('.enableSubscription', 'click', function (e) {
				e.preventDefault();
				PerformAsyncAction($(this), getSubmitCallback(options.actions.enableSubscription), $('#subscriptionIndicator'), subscriptionCallback);
			});

			details.delegate('.disableSubscription', 'click', function (e) {
				e.preventDefault();
				PerformAsyncAction($(this), getSubmitCallback(options.actions.disableSubscription), $('#subscriptionIndicator'), subscriptionCallback);
			});
		});

		elements.checkAllResources.click(function (e) {
			e.preventDefault();
			elements.bulkUpdateList.find('input:checkbox').prop('checked', true);
		});

		elements.checkNoResources.click(function (e) {
			e.preventDefault();
			elements.bulkUpdateList.find('input:checkbox').prop('checked', false);
		});

		elements.checkAllDeleteResources.click(function (e) {
			e.preventDefault();
			elements.bulkDeleteList.find('input:checkbox').prop('checked', true);
		});

		elements.checkNoDeleteResources.click(function (e) {
			e.preventDefault();
			elements.bulkDeleteList.find('input:checkbox').prop('checked', false);
		});

		$(".save").click(function () {
			$(this).closest('form').submit();
		});

		$(".cancel").click(function () {
			$(this).closest('.modal').modal("hide");
		});

		elements.addResourceButtons.click(function (e) {
			e.preventDefault();
			elements.addResourceDialog.modal('show');
			$('#resourceName').focus();
		});

		elements.bulkEditStatusOptions.change(function (e) {
			populateReasonOptions(elements.bulkEditStatusOptions.val(), elements.bulkEditStatusReasons);
		});

		elements.statusOptionsFilter.change(function (e) {
			populateReasonOptions(elements.statusOptionsFilter.val(), elements.statusReasonsFilter);
		});

		elements.clearFilterButton.click(function (e) {
			e.preventDefault();
			elements.filterTable.find('input,select,textarea').val('');

			filterResources();
		});

		wireUpCheckboxToggle(elements.bulkUpdateDialog);

		$('#bulkEditEnableCheckIn').change(function () {
			if ($(this).val() == '1')
			{
				$('#bulkUpdateAutoReleaseMinutesDiv').removeClass('no-show');
			}

			if ($(this).val() == '0')
			{
				$('#bulkUpdateAutoReleaseMinutesDiv').addClass('no-show');
			}
		});

		elements.bulkUpdatePromptButton.click(function (e) {
			e.preventDefault();

			var items = [];
			elements.bulkUpdateList.empty();
			$.each(resources, function (i, r) {
				var checkId = 'bulk' + r.id;
				items.push('<div class="checkbox checkbox-inline">' + '<input type="checkbox" id="' + checkId + '" name="resourceId[]" checked="checked" value="' + r.id + '" />' + '<label for="' + checkId + '">' + r.name + '</label>' + '</div>');
			});
			$('<div/>', {html: items.join('')}).appendTo(elements.bulkUpdateList);

			$('#bulkUpdateDialog').modal('show');
		});

		elements.bulkDeletePromptButton.click(function (e) {
			e.preventDefault();

			var items = [];
			elements.bulkDeleteList.empty();
			$.each(resources, function (i, r) {
				var checkId = 'bulk' + r.id;
				items.push('<div class="checkbox checkbox-inline">' + '<input type="checkbox" id="' + checkId + '" name="resourceId[]" checked="checked" value="' + r.id + '" />' + '<label for="' + checkId + '">' + r.name + '</label>' + '</div>');
			});
			$('<div/>', {html: items.join('')}).appendTo(elements.bulkDeleteList);

			$('#bulkDeleteDialog').modal('show');
		});

		elements.userSearch.userAutoComplete(options.userAutocompleteUrl, function (ui) {
			changeUserPermission(ui.item.value, '1');
			elements.userSearch.val('');
		});

		elements.groupSearch.groupAutoComplete(options.groupAutocompleteUrl, function (ui) {
			changeGroupPermission(ui.item.value, '1');
			elements.groupSearch.val('');
		});

		elements.browseUsersButton.click(function (e) {
			e.preventDefault();
			showAllUsersToAdd();
		});

		elements.resourceUserList.delegate('.change-permission-type', 'change', function (e) {
			e.preventDefault();
			var userId = $(this).data('user-id');
			var type = $(this).val();
			changeUserPermission(userId, type);
		});

		elements.allUsersList.delegate('.change-permission-type', 'change', function (e) {
			e.preventDefault();
			var userId = $(this).data('user-id');
			var type = $(this).val();
			changeUserPermission(userId, type);
		});

		elements.browseGroupsButton.click(function (e) {
			e.preventDefault();
			showAllGroupsToAdd();
		});

		elements.resourceGroupList.delegate('.change-permission-type', 'change', function (e) {
			e.preventDefault();
			var groupId = $(this).data('group-id');
			var type = $(this).val();
			changeGroupPermission(groupId, type);
		});

		elements.allGroupsList.delegate('.change-permission-type', 'change', function (e) {
			e.preventDefault();
			var groupId = $(this).data('group-id');
			var type = $(this).val();
			changeGroupPermission(groupId, type);
		});

		elements.autoAssign.on('click', function () {
			elements.removeAllPermissions.find('input').prop('checked', false);
			if (!elements.autoAssign.is(':checked'))
			{
				elements.removeAllPermissions.removeClass('no-show');
			}
			else
			{
				elements.removeAllPermissions.addClass('no-show');
			}
		});

		elements.enableCheckIn.on('click', function () {
			showHideAutoRelease();
		});

		elements.checkAllowConcurrent.on('click', function () {
			showHideConcurrent();
		});

		wireUpCheckboxToggle(elements.durationForm);
		wireUpCheckboxToggle(elements.capacityForm);
		wireUpCheckboxToggle(elements.accessForm);
		wireUpCheckboxToggle(elements.bulkUpdateForm);

		elements.importTrigger.click(function (e) {
			e.preventDefault();
			$('#importErrors').empty().addClass('no-show');
			$('#importResults').addClass('no-show');
			elements.importDialog.modal('show');
		});

		elements.imageDialog.delegate('.defaultImage', 'click', function (e) {
			e.preventDefault();
			var image = $(e.target).closest('.resource-image').attr('id');
			elements.defaultImageName.val(image);
			elements.defaultImageForm.submit();
		});

		elements.imageDialog.delegate('.deleteImage', 'click', function (e) {
			e.preventDefault();
			var image = $(e.target).closest('.resource-image').attr('id');
			elements.removeImageName.val(image);
			elements.removeImageForm.submit();
		});

		elements.imageDialog.on('hidden.bs.modal', function () {
			window.location.reload();
		});

		var imageSaveErrorHandler = function (result) {
			alert(result);
		};

		var combineIntervals = function (jqForm, opts) {
			$(jqForm).find('.interval').each(function (i, v) {
				var id = $(v).attr('id');
				var d = $('#' + id + 'Days').val();
				var h = $('#' + id + 'Hours').val();
				var m = $('#' + id + 'Minutes').val();
				$(v).val(d + 'd' + h + 'h' + m + 'm');
			});
		};

		var bulkUpdateErrorHandler = function (result) {
			$("#bulkUpdateErrors").html(result).show();
		};

		var errorHandler = function (result) {
			$("#globalError").html(result).show();
		};

		var importHandler = function (responseText, form) {
			if (!responseText)
			{
				return;
			}

			$('#importCount').text(responseText.importCount);
			$('#importSkipped').text(responseText.skippedRows.length > 0 ? responseText.skippedRows.join(',') : '0');
			$('#importResult').removeClass('no-show');

			var errors = $('#importErrors');
			errors.empty();
			if (responseText.messages && responseText.messages.length > 0)
			{
				var messages = responseText.messages.join('</li><li>');
				errors.html('<div>' + messages + '</div>').removeClass('no-show');
			}
		};

		ConfigureAsyncForm(elements.imageForm, defaultSubmitCallback(elements.imageForm), function (data) {
			onImagesUpdated(data);
		}, imageSaveErrorHandler);

		ConfigureAsyncForm(elements.removeImageForm, defaultSubmitCallback(elements.removeImageForm), function (data) {
			onImagesUpdated(data);
		});

		ConfigureAsyncForm(elements.defaultImageForm, defaultSubmitCallback(elements.defaultImageForm), function (data) {
			onImagesUpdated(data);
		});

		ConfigureAsyncForm(elements.addForm, defaultSubmitCallback(elements.addForm), null, handleAddError);
		ConfigureAsyncForm(elements.deleteForm, defaultSubmitCallback(elements.deleteForm), function (result) {
			var id = getActiveResourceId();
			$('#resourceList').find('[data-resourceid="' + id + '"]').remove();
			elements.deleteDialog.modal('hide');
		});
		ConfigureAsyncForm(elements.durationForm, defaultSubmitCallback(elements.durationForm), onDurationSaved, null, {onBeforeSerialize: combineIntervals});
		ConfigureAsyncForm(elements.capacityForm, defaultSubmitCallback(elements.capacityForm), onCapacitySaved);
		ConfigureAsyncForm(elements.accessForm, defaultSubmitCallback(elements.accessForm), onAccessSaved, null, {onBeforeSerialize: combineIntervals});
		ConfigureAsyncForm(elements.bulkUpdateForm, defaultSubmitCallback(elements.bulkUpdateForm), null, bulkUpdateErrorHandler, {onBeforeSerialize: combineIntervals});
		ConfigureAsyncForm(elements.changeUserForm, defaultSubmitCallback(elements.changeUserForm), changeUserPermissions, errorHandler);
		ConfigureAsyncForm(elements.changeGroupForm, defaultSubmitCallback(elements.changeGroupForm), changeGroupPermissions, errorHandler);
		ConfigureAsyncForm(elements.resourceGroupForm, defaultSubmitCallback(elements.resourceGroupForm), onResourceGroupsSaved);
		ConfigureAsyncForm(elements.colorForm, defaultSubmitCallback(elements.colorForm), function () {
		});
		ConfigureAsyncForm(elements.creditsForm, defaultSubmitCallback(elements.creditsForm), onCreditsSaved, null, errorHandler);
		ConfigureAsyncForm(elements.copyForm, defaultSubmitCallback(elements.copyForm));
		ConfigureAsyncForm(elements.importForm, defaultSubmitCallback(elements.importForm), importHandler);
		ConfigureAsyncForm(elements.bulkDeleteForm, defaultSubmitCallback(elements.bulkDeleteForm));
	};

	ResourceManagement.prototype.add = function (resource) {
		resources[resource.id] = resource;
	};

	ResourceManagement.prototype.addStatusReason = function (id, statusId, description) {
		if (!(statusId in reasons))
		{
			reasons[statusId] = [];
		}

		reasons[statusId].push({id: id, description: description});
	};

	ResourceManagement.prototype.initializeStatusFilter = function (statusId, reasonId) {
		elements.statusOptionsFilter.val(statusId);
		elements.statusOptionsFilter.trigger('change');
		elements.statusReasonsFilter.val(reasonId);
	};

	ResourceManagement.prototype.addResourceGroups = function (resourceGroups) {
		elements.groupDiv.tree({
			data: resourceGroups, saveState: false, dragAndDrop: false, selectable: false, autoOpen: true,

			onCreateLi: function (node, $li) {
				var span = $li.find('span');
				var itemName = span.text();
				var id = 'group_id' + node.id;

				var label = $('<div class="checkbox inline"><input group-id="' + node.id + '" name="group_id[]" type="checkbox" id="' + id + '" value="' + node.id + '"/><label for="' + id + '">' + itemName + '</label></div>');

				$li.find('span').html(label);
			}
		});
	};

	var getSubmitCallback = function (action) {
		return function () {
			return options.submitUrl + "?rid=" + getActiveResourceId() + "&action=" + action;
		};
	};

	var defaultSubmitCallback = function (form) {
		return function () {
			return options.submitUrl + "?action=" + form.attr('ajaxAction') + '&rid=' + getActiveResourceId();
		};
	};

	var setActiveResourceId = function (resourceId) {
		elements.activeId.val(resourceId);
	};

	var getActiveResourceId = function () {
		return elements.activeId.val();
	};

	var getResource = function (id) {
		return resources[id];
	};

	var getActiveResource = function () {
		return getResource(getActiveResourceId());
	};

	var refreshResourceImages = function (resource) {
		var imageContainer = $('#resource-images');
		imageContainer.empty();

		var imageContents = function (image, isDefault) {
			imageContainer.append('<div class="resource-image col-xs-4" id="' + image + '"><img src="' + image + '"/><div class="center">' + (isDefault ? 'Default' : '<a href="#" class="defaultImage">Default</a>') + ' | <a href="#" class="deleteImage"><span class="fa fa-trash"></span> Remove</a></div></div>');
		};

		if (resource.image)
		{
			imageContents(resource.image, true);
		}

		for (var i = 0; i < resource.images.length; i++)
		{
			imageContents(resource.images[i]);
		}
	};

	var showChangeImage = function (e) {
		e.preventDefault();
		var resource = getActiveResource();
		refreshResourceImages(resource);
		elements.imageDialog.modal("show");
	};

	var onImagesUpdated = function (resource) {
		refreshResourceImages(resource);
		getActiveResource().image = resource.image;
		getActiveResource().images = resource.images;
	};

	var showResourceAdmin = function (e) {
		$('#adminGroupId').val(getActiveResource().adminGroupId);
		elements.groupAdminDialog.modal('show');
	};

	var showDeletePrompt = function (e) {
		e.preventDefault();
		elements.deleteDialog.modal('show');
	};

	var showDurationPrompt = function (e) {
		var resource = getActiveResource();

		setDaysHoursMinutes('#minDuration', resource.minLength, $('#noMinimumDuration'));
		setDaysHoursMinutes('#maxDuration', resource.maxLength, $('#noMaximumDuration'));
		setDaysHoursMinutes('#bufferTime', resource.bufferTime, $('#noBufferTime'));
		$('#allowMultiDay').prop('checked', resource.allowMultiday && resource.allowMultiday == "1");

		elements.durationDialog.modal('show');
	};

	var showCapacityPrompt = function (e) {
		var resource = getActiveResource();

		showHideConfiguration(resource.maxParticipants, $('#maxCapacity'), $('#unlimitedCapacity'));
		elements.capacityDialog.modal('show');
	};

	var showAccessPrompt = function (e) {
		var resource = getActiveResource();

		setDaysHoursMinutes('#startNoticeAdd', resource.startNoticeAdd, $('#noStartNoticeAdd'));
		setDaysHoursMinutes('#startNoticeUpdate', resource.startNoticeUpdate, $('#noStartNoticeUpdate'));
		setDaysHoursMinutes('#startNoticeDelete', resource.startNoticeDelete, $('#noStartNoticeDelete'));
		setDaysHoursMinutes('#endNotice', resource.endNotice, $('#noEndNotice'));

		$('#requiresApproval').prop('checked', resource.requiresApproval && resource.requiresApproval == "1");

		elements.autoAssign.prop('checked', resource.autoAssign && resource.autoAssign == "1");
		elements.removeAllPermissions.addClass('no-show');

		elements.enableCheckIn.prop('checked', resource.enableCheckin && resource.enableCheckin == "1");
		elements.autoReleaseMinutes.val(resource.autoReleaseMinutes);
		showHideAutoRelease();

		elements.checkAllowConcurrent.prop('checked', resource.allowConcurrent && resource.allowConcurrent == "1");
		elements.maxConcurrent.val(Math.max(2, Number.parseInt(resource.maxConcurrent)));
		showHideConcurrent();

		elements.accessDialog.modal('show');
	};

	var showHideAutoRelease = function () {
		if (!elements.enableCheckIn.is(':checked'))
		{
			elements.autoReleaseMinutesDiv.addClass('no-show');
		}
		else
		{
			elements.autoReleaseMinutesDiv.removeClass('no-show');
		}
	};

	var showHideConcurrent = function () {
		if (elements.checkAllowConcurrent.is(':checked'))
		{
			elements.allowConcurrentDiv.removeClass('no-show');
		}
		else
		{
			elements.allowConcurrentDiv.addClass('no-show');
		}
	}
	var setDuration = function (container, resourceDuration) {
		var emptyIfZero = function (val) {
			if (val == 0)
			{
				return '';
			}
			return val;
		};
		resourceDuration.value = container.attr('data-value');
		resourceDuration.days = emptyIfZero(container.attr('data-days'));
		resourceDuration.hours = emptyIfZero(container.attr('data-hours'));
		resourceDuration.minutes = emptyIfZero(container.attr('data-minutes'));
	};

	var onDurationSaved = function (resultHtml) {
		var resource = getActiveResource();
		var resourceDiv = $("div[data-resourceId=" + resource.id + "]");
		resourceDiv.find('.durationPlaceHolder').html(resultHtml);

		var result = resourceDiv.find('.durationPlaceHolder');
		var minDuration = result.find('.minDuration');
		var maxDuration = result.find('.maxDuration');
		var bufferTime = result.find('.bufferTime');
		var allowMultiDay = result.find('.allowMultiDay');

		setDuration(minDuration, resource.minLength);
		setDuration(maxDuration, resource.maxLength);
		setDuration(bufferTime, resource.bufferTime);
		resource.allowMultiday = allowMultiDay.attr('data-value');

		elements.durationDialog.modal('hide');
	};

	var onCapacitySaved = function (resultHtml) {
		var resource = getActiveResource();
		var resourceDiv = $("div[data-resourceId=" + resource.id + "]");
		resourceDiv.find('.capacityPlaceHolder').html(resultHtml);

		var result = resourceDiv.find('.capacityPlaceHolder');
		var maxParticipants = result.find('.maxParticipants');
		resource.maxParticipants = maxParticipants.attr('data-value');

		elements.capacityDialog.modal('hide');
	};

	var onAccessSaved = function (resultHtml) {
		var resource = getActiveResource();
		var resourceDiv = $("div[data-resourceId=" + resource.id + "]");
		resourceDiv.find('.accessPlaceHolder').html(resultHtml);

		var result = resourceDiv.find('.accessPlaceHolder');
		var startNoticeAdd = result.find('.startNoticeAdd');
		var startNoticeUpdate = result.find('.startNoticeUpdate');
		var startNoticeDelete = result.find('.startNoticeDelete');
		var endNotice = result.find('.endNotice');
		var requiresApproval = result.find('.requiresApproval');
		var autoAssign = result.find('.autoAssign');
		var enableCheckin = result.find('.enableCheckin');
		var autoRelease = result.find('.autoRelease');
		var maxConcurrent = result.find('.allowConcurrent');

		setDuration(startNoticeAdd, resource.startNoticeAdd);
		setDuration(startNoticeUpdate, resource.startNoticeUpdate);
		setDuration(startNoticeDelete, resource.startNoticeDelete);
		setDuration(endNotice, resource.endNotice);
		resource.requiresApproval = requiresApproval.attr('data-value');
		resource.autoAssign = autoAssign.attr('data-value');

		resource.enableCheckin = enableCheckin.attr('data-value');
		resource.autoReleaseMinutes = autoRelease.attr('data-value');

		resource.maxConcurrent = maxConcurrent.data('max-concurrent');
		resource.allowConcurrent = maxConcurrent.data('allow-concurrent');

		elements.accessDialog.modal('hide');
	};

	var onResourceGroupsSaved = function (resultHtml) {
		var resource = getActiveResource();
		var resourceDiv = $("div[data-resourceId=" + resource.id + "]");
		resourceDiv.find('.resourceGroupsPlaceHolder').html(resultHtml);

		var result = resourceDiv.find('.resourceGroupsPlaceHolder');
		var groupIdElements = result.find('.resourceGroupId');

		var groupIds = [];
		$.each(groupIdElements, function (i, group) {
			groupIds.push($(group).attr('data-value'));
		});
		resource.resourceGroupIds = groupIds;

		elements.resourceGroupDialog.modal('hide');
	};

	var onCreditsSaved = function (resultHtml) {
		var resource = getActiveResource();
		var resourceDiv = $("div[data-resourceId=" + resource.id + "]");
		resourceDiv.find('.creditsPlaceHolder').html(resultHtml);

		var credits = resourceDiv.find('.creditsPerSlot');
		var peak = resourceDiv.find('.peakCreditsPerSlot');

		resource.credits = credits.attr('data-value');
		resource.peakCredits = peak.attr('data-value');

		elements.creditsDialog.modal('hide');
	};

	var showStatusPrompt = function (e) {
		var resource = getActiveResource();
		var statusForm = $('.popover:visible').find('form');

		var statusOptions = statusForm.find(elements.statusOptions);
		var statusReasons = statusForm.find(elements.statusReasons);
		var addStatusReason = statusForm.find(elements.addStatusReason);
		var saveButton = statusForm.find('.save');

		statusOptions.val(resource.statusId);
		statusReasons.val(resource.reasonId);

		statusOptions.unbind();
		statusOptions.change(function (e) {
			populateReasonOptions(statusOptions.val(), statusReasons);
		});

		populateReasonOptions(statusOptions.val(), statusReasons);

		addStatusReason.unbind();
		addStatusReason.click(function (e) {
			e.preventDefault();
			statusForm.find(elements.newStatusReason).toggleClass('no-show');
			statusForm.find(elements.existingStatusReason).toggleClass('no-show');

			if (statusForm.find(elements.newStatusReason).hasClass('no-show'))
			{
				statusForm.find(elements.statusReasons).data('prev', statusReasons.val());
				statusForm.find(elements.statusReasons).val('');
				statusForm.find(elements.resourceStatusReason).focus();
			}
			else
			{
				statusForm.find(elements.statusReasons).val(statusReasons.data('prev'));
				statusForm.find(elements.statusReasons).focus();
			}
		});

		saveButton.unbind();

		ConfigureAsyncForm(statusForm, defaultSubmitCallback(statusForm));

		saveButton.click(function () {
			statusForm.submit();
		});

		statusOptions.focus();
	};

	function populateReasonOptions(statusId, reasonsElement) {
		reasonsElement.empty().append($('<option>', {value: '', text: '-'}));

		if (statusId in reasons)
		{
			$.each(reasons[statusId], function (i, v) {
				reasonsElement.append($('<option>', {
					value: v.id, text: v.description
				}));
			});
		}
	}

	function setDaysHoursMinutes(elementPrefix, interval, attributeCheckbox) {
		if (!interval)
		{
			interval = {days: null, hours: null, minutes: null, value: null};
		}
		$(elementPrefix + 'Days').val(interval.days);
		$(elementPrefix + 'Hours').val(interval.hours);
		$(elementPrefix + 'Minutes').val(interval.minutes);

		showHideConfiguration(interval.value, $(elementPrefix), attributeCheckbox);

	}

	function showHideConfiguration(attributeValue, attributeDisplayElement, attributeCheckbox) {
		attributeDisplayElement.val(attributeValue);
		var selector = attributeCheckbox.attr('data-related-inputs');
		var container = attributeCheckbox.closest('form');
		var span = container.find(selector);

		if (attributeValue == '' || attributeValue == undefined)
		{
			attributeCheckbox.prop('checked', true);
			span.hide();
		}
		else
		{
			attributeCheckbox.prop('checked', false);
			span.show();
		}
	}

	function wireUpCheckboxToggle(container) {
		container.find(':checkbox').change(function () {
			var selector = $(this).attr('data-related-inputs');
			var span = container.find(selector);

			if ($(this).is(":checked"))
			{
				span.find("input[type=text],input[type=number]").val('');
				span.hide();
			}
			else
			{
				span.show();
			}
		});
	}

	var handleAddError = function (result) {
		$('#addResourceResults').text(result).show();
	};

	var changeUserPermissions = function () {
		var resourceId = getActiveResourceId();
		$.get(opts.permissionsUrl + '?dr=users', {rid: resourceId}, function (data) {
			elements.resourceUserList.html(data);
			$('.user-permission-spinner').addClass('no-show');
		});
	};

	var changeUserPermission = function (userId, type) {
		$('.user-permission-spinner').removeClass('no-show');
		$('#changeUserId').val(userId);
		$('#changeUserType').val(type);
		elements.changeUserForm.submit();
	};

	var showAllUsersToAdd = function () {
		elements.userDialog.modal('hide');
		elements.allUsersList.empty();

		var resourceId = getActiveResourceId();
		$.get(opts.permissionsUrl + '?dr=usersAll', {rid: resourceId}, function (data) {
			elements.allUsersList.html(data);
		});

		elements.browseUserDialog.modal('show');
	};

	var changeGroupPermissions = function () {
		var resourceId = getActiveResourceId();
		$.get(opts.permissionsUrl + '?dr=groups', {rid: resourceId}, function (data) {
			elements.resourceGroupList.html(data);
			$('.group-permission-spinner').addClass('no-show');
		});
	};

	var changeGroupPermission = function (groupId, type) {
		$('.group-permission-spinner').removeClass('no-show');
		$('#changeGroupId').val(groupId);
		$('#changeGroupType').val(type);
		elements.changeGroupForm.submit();
	};

	var showAllGroupsToAdd = function () {
		elements.groupDialog.modal('hide');
		elements.allGroupsList.empty();

		var resourceId = getActiveResourceId();
		$.get(opts.permissionsUrl + '?dr=groupsAll', {rid: resourceId}, function (data) {
			elements.allGroupsList.html(data);
		});

		elements.browseGroupDialog.modal('show');
	};

	function changeResourceGroups() {
		var resource = getActiveResource();

		elements.groupDiv.find(':checked').prop('checked', false);

		$.each(resource.resourceGroupIds, function (i, id) {
			elements.groupDiv.find('[group-id=' + id + ']').prop('checked', true);
		});
	}
}
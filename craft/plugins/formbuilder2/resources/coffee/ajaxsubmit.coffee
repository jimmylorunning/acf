$(document).ready ->
  notificationContainer = $('.formbuilder2 .notifications')
  theForm = $('form.formbuilder2')

  # AJAX Form Submit
  theForm.submit (e) ->
    notificationContainer.html ''
    e.preventDefault()
    url = '/actions/' + $(@).children('[name=action]').attr('value')
    redirect = $(@).children('[name=formRedirect]').attr('data-custom-redirect')
    redirectUrl = $(@).children('[name=formRedirect]').attr('value')
    data = $(this).serialize()

    # Start Loading
    notificationContainer.html '<p>Sending...</p>'
    $.post url, data, (response) ->
      if response.success
        if redirect == '1' 
          window.location.href = redirectUrl
        else
          notificationContainer.html '<p class="success-message">' + response.customSuccessMessage + '</p>'
          theForm[0].reset()
      else
        notificationContainer.html '<p class="error-message">' + response.customErrorMessage + '</p>'
        errorsContainer = $('.notifications').append('<ul class="errors"></ul>').find('ul.errors')
        $.each response.validationErrors, (index, value) ->
          errorsContainer.append '<li>'+value+'</li>'




var App,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

App = (function() {
  function App() {
    this.newForm = bind(this.newForm, this);
    this.init = bind(this.init, this);
  }

  App.prototype.init = function() {
    var clipboard, navTrigger, sidebar;
    if ($('.formbuilder').length > 0) {
      sidebar = $('#sidebar .primary');
      navTrigger = $('.nav-trigger');
      navTrigger.on('click', function(e) {
        e.preventDefault();
        return sidebar.slideToggle();
      });
    }
    clipboard = new Clipboard('.copy');
    return clipboard.on('success', function(e) {
      return e.clearSelection();
    });
  };

  App.prototype.newForm = function() {
    var newFormActiveTab;
    if ($('.fb-new-form').length > 0) {
      newFormActiveTab = Cookies.get('newform-active-tab');
      $('.notification-tabs a').click(function(event) {
        var tab;
        event.preventDefault();
        $(this).parent().addClass('current');
        $(this).parent().siblings().removeClass('current');
        tab = $(this).attr('href');
        $('.email-tab-content').not(tab).css('display', 'none');
        return $(tab).fadeIn();
      });
      $('.menu-tabs a').click(function(event) {
        var tab;
        event.preventDefault();
        $(this).parent().addClass('current');
        $(this).parent().siblings().removeClass('current');
        tab = $(this).attr('href');
        Cookies.set('newform-active-tab', tab, {
          expires: 7
        });
        $('.tab-content').not(tab).css('display', 'none');
        return $(tab).fadeIn();
      });
      if ($('#form-settings').find('.errors').length > 0) {
        $('.tab-toggle-form-settings').addClass('has-errors');
      }
      if ($('#spam-protection').find('.errors').length > 0) {
        $('.tab-toggle-spam-protection').addClass('has-errors');
      }
      if ($('#messages').find('.errors').length > 0) {
        $('.tab-toggle-messages').addClass('has-errors');
      }
      if ($('#notify').find('.errors').length > 0) {
        $('.tab-toggle-notify').addClass('has-errors');
      }
      if ($('.has-errors').length > 0) {
        $('.menu-tabs h2').removeClass('current');
        $('.has-errors').first().addClass('current').find('a').trigger('click');
      }
      if ($('#emailTemplateStyle2').is(':checked')) {
        $('#html-template-extra').slideDown();
      }
      $('#emailTemplateStyle-field input').on('change', function(e) {
        var val;
        val = $(this).val();
        if (val === 'html') {
          return $('#html-template-extra').slideDown();
        } else {
          return $('#html-template-extra').slideUp();
        }
      });
      if ($('#saveSubmissionsToDatabase').is(':checked')) {
        $('.method-database .checkbox-toggle').addClass('selected');
        $('.method-database .checkbox-extra').show();
      }
      if ($('#hasFileUploads').is(':checked')) {
        $('.method-files .checkbox-toggle').addClass('selected');
        $('.method-files .checkbox-extra').show();
      }
      if ($('#customRedirect').is(':checked')) {
        $('.method-redirect .checkbox-toggle').addClass('selected');
        $('.method-redirect .checkbox-extra').show();
      }
      if ($('#ajaxSubmit').is(':checked')) {
        $('.method-ajax .checkbox-toggle').addClass('selected');
      }
      if ($('#spamTimeMethod').is(':checked')) {
        $('.method-time .checkbox-toggle').addClass('selected');
        $('.method-time .checkbox-extra').show();
      }
      if ($('#spamHoneypotMethod').is(':checked')) {
        $('.method-honeypot .checkbox-toggle').addClass('selected');
        $('.method-honeypot .checkbox-extra').show();
      }
      if ($('#notifySubmission').is(':checked')) {
        $('.method-notify .checkbox-toggle').addClass('selected');
        $('.method-notify .checkbox-extra').show();
      }
      return $('.checkbox-toggle').on('click', function() {
        var toggle;
        toggle = $(this).data('checkbox');
        $(this).toggleClass('selected');
        if ($(this).hasClass('selected')) {
          $('#' + toggle).prop('checked', true);
          return $(this).next('.checkbox-extra').stop().slideDown();
        } else {
          $('#' + toggle).prop('checked', false);
          return $(this).next('.checkbox-extra').stop().slideUp();
        }
      });
    }
  };

  return App;

})();

$(document).ready(function() {
  var Application;
  Application = new App();
  Application.init();
  return Application.newForm();
});

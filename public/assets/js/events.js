$( document ).ready( function() {

  isotopeOptions = {};
  defaultOptions = {
    filter: '*',
    layoutMode: 'masonry'
  };

  // init Isotope
  var optionSets = $('#filters');
  var container = $('.events_grid');
  container.isotope(defaultOptions);
  var isOptionLinkClicked = false;

  /**** TABS *****/
  $('#filtertabs a').click(function (e) {
    e.preventDefault();
    clicked = $(this).attr('name');
    if (clicked == 'rides') {
      var option = { 'track': '.ride' };
      $.extend( isotopeOptions, option );
      $.bbq.pushState( isotopeOptions );
      $('#tracks').children().removeClass("selected");
      isOptionClicked = true;
    } else {
      $.bbq.removeState( ['track'] );
      $.bbq.removeState( ['difficulty'] );
      $('#rides').children().removeClass("selected");
      isOptionClicked = true;
    }
  })

  /**** FILTER BUTTON GROUPS ****/
  $('.date-group').find('a').click(function(){
    var $this = $(this);
    changeSelectedLink( $this, '.date-group' );
    var href = $this.attr('href').replace( /^#/, '' ),
        option = $.deparam( href, true );
    $.extend( isotopeOptions, option );
    $.bbq.pushState( isotopeOptions );
    isOptionLinkClicked = true;
    return false;
  });

  $('#tracks').find('a').click(function(){
    var $this = $(this);
    changeSelectedLink( $this, '#tracks' );
    var href = $this.attr('href').replace( /^#/, '' ),
        option = $.deparam( href, true );
    $.extend( isotopeOptions, option );
    $.bbq.pushState( isotopeOptions );
    isOptionLinkClicked = true;
    return false;
  });

  $('#rides').find('a').click(function(){
    var $this = $(this);
    changeSelectedLink( $this, '#rides' );
    var href = $this.attr('href').replace( /^#/, '' ),
        option = $.deparam( href, true );
    $.extend( isotopeOptions, option );
    $.bbq.pushState( isotopeOptions );
    isOptionLinkClicked = true;
    return false;
  });

  function changeSelectedLink( $elem, $parentClass) {
    $elem.parents($parentClass).find('.selected').removeClass('selected');
    $elem.addClass('selected');
  }

  var hashChanged = false;

  /**** EVENT: on hashchange ****/
  $(window).bind( 'hashchange', function( event ){
    var hashOptions = window.location.hash ? $.deparam.fragment( window.location.hash, true ) : {};
    // do not animate first call
    var aniEngine = hashChanged ? 'best-available' : 'none';
    var combinedOptions = hashOptions;
    isotopeOptions = hashOptions;
    if (typeof isotopeOptions['date'] == 'undefined') {
      hashOptions['date'] = '*';
    }
    if (typeof isotopeOptions['track'] == 'undefined') {
      hashOptions['track'] = '';
    }
    combinedOptions['filter'] = hashOptions['date'] + hashOptions['track'];
    if (isotopeOptions['track'] == '.ride') {
      if (typeof isotopeOptions['difficulty'] == 'undefined') {
        hashOptions['difficulty'] = '';
      }
      combinedOptions['filter'] = combinedOptions['filter'] + hashOptions['difficulty'];
    }

    var options = $.extend( {}, defaultOptions, combinedOptions, { animationEngine: aniEngine } );
    container.isotope( options );
    $.bbq.pushState( combinedOptions);
    isotopeOptions = hashOptions;

    // if option link was not clicked
    // then we'll need to update selected links
    if ( !isOptionLinkClicked ) {
      var hrefObj, hrefValue, $selectedLink;
        for ( var key in options ) {
          hrefObj = {};
          hrefObj[ key ] = options[ key ];
          // convert object into parameter string
          // i.e. { filter: '.inner-transition' } -> 'filter=.inner-transition'
          hrefValue = $.param( hrefObj );
          // get matching link
          if (hrefValue == 'track=.ride') {
            $('#filtertabs a:last').tab('show'); 
          }
          $selectedLink = optionSets.find('a[href="#' + hrefValue + '"]');
          console.log("hash sl = " + $selectedLink);
          changeSelectedLink( $selectedLink , key + '-group');
        }
      }
  
      // display message box if no filtered items
     if ( !container.data('isotope').filteredItems.length ) {
        $('#no-results').show();
      } else {
        $('#no-results').hide();
      }

      isOptionLinkClicked = false;
      hashChanged = true;
    })
    // trigger hashchange to capture any hash data on init
    .trigger('hashchange');
});

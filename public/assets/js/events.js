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

      $('.date-group').find('a').click(function(){
        var $this = $(this);
        changeSelectedLink( $this, '.date-group' );
            // get href attr, remove leading #
        var href = $this.attr('href').replace( /^#/, '' ),
            // convert href into object
            // i.e. 'filter=.inner-transition' -> { filter: '.inner-transition' }
            option = $.deparam( href, true );
        // apply new option to previous
        $.extend( isotopeOptions, option );
        // set hash, triggers hashchange on window
        $.bbq.pushState( isotopeOptions );
        isOptionLinkClicked = true;
        return false;
      });

      $('.track-group').find('a').click(function(){
        var $this = $(this);
        changeSelectedLink( $this, '.track-group' );
        var href = $this.attr('href').replace( /^#/, '' ),
            option = $.deparam( href, true );
        $.extend( isotopeOptions, option );

        console.log('option = ' + option['track']);
        $.bbq.pushState( isotopeOptions );
        if (option['track'] != '.ride') {
          $.bbq.removeState(['difficulty']);
          hideDifficulty();
        } else {
          showDifficulty();
        }
        isOptionLinkClicked = true;
        return false;
      });

      $('.difficulty-group').find('a').click(function(){
        var $this = $(this);
        changeSelectedLink( $this, '.difficulty-group' );
        var href = $this.attr('href').replace( /^#/, '' ),
            option = $.deparam( href, true );
        $.extend( isotopeOptions, option );
        $.bbq.pushState( isotopeOptions );
        isOptionLinkClicked = true;
        return false;
      });

      function changeSelectedLink( $elem, $parentClass) {
        // remove selected class on previous item
        $elem.parents($parentClass).find('.selected').removeClass('selected');
        // set selected class on new item
        $elem.addClass('selected');
      }

      function showDifficulty() {
        $('.difficulty-group').show();
      }

      function hideDifficulty() {
        $('.difficulty-group').hide();
        changeSelectedLink($('#difficulty-all'), '.difficulty-group');
      }

      var hashChanged = false;

      $(window).bind( 'hashchange', function( event ){
        console.log('in hashchange');
        // get options object from hash
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

        // apply defaults where no option was specified
        var options = $.extend( {}, defaultOptions, combinedOptions, { animationEngine: aniEngine } );
        // apply options from hash
        container.isotope( options );
        $.bbq.pushState( combinedOptions);
        // save options

        isotopeOptions = hashOptions;

        // if option link was not clicked
        // then we'll need to update selected links
        if ( !isOptionLinkClicked ) {
          if (hashOptions['track'] != '.ride') {
            hideDifficulty();
          }
          // iterate over options
          var hrefObj, hrefValue, $selectedLink;
          for ( var key in options ) {
            hrefObj = {};
            hrefObj[ key ] = options[ key ];
            // convert object into parameter string
            // i.e. { filter: '.inner-transition' } -> 'filter=.inner-transition'
            hrefValue = $.param( hrefObj );
            // get matching link
            $selectedLink = optionSets.find('a[href="#' + hrefValue + '"]');
            changeSelectedLink( $selectedLink , key + '-group');
          }
        }
    
        isOptionLinkClicked = false;
        hashChanged = true;
      })
      // trigger hashchange to capture any hash data on init
      .trigger('hashchange');
});

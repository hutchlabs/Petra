
(function(){
        // if firefox 3.5+, hide content till load (or 3 seconds) to prevent FOUT
        var d = document, e = d.documentElement, s = d.createElement('style');
        if (e.style.MozTransform === ''){ // gecko 1.9.1 inference
	
          s.textContent = '#siteNavigation li.navItem > a, #siteNavigation ul.menu a .projectTitle, H1, H2, div.projectTotals, ul.tileList a.tileName, .upgradePlan h2, .upgradePlan h3   {   visibility: hidden !important; }';
	
          var r = document.getElementsByTagName('script')[0];
          r.parentNode.insertBefore(s, r);
          function f(){ s.parentNode && s.parentNode.removeChild(s); }
          addEventListener('load',f,false);
          setTimeout(f,3000); 
        }})();
	        
			
		// hover stuff for navigation menus
		$( "#siteNavigation" ).find( "li.menuNavItem" ).hover(
			function(){
				$( this ).addClass( "activeMenuNavItem" );
			},
			function(){
					$( this ).removeClass( "activeMenuNavItem" );
				}
			);

			var userNavigation = $( "#userNavigation" );
			var userNavigationPrimaryNavigation = userNavigation.find( ".primaryNavigation" );
			var userNavigationSecondaryNavigation = userNavigation.find( ".secondaryNavigation" );

			// Get the menu nav items.
			var userNavigationNavItems = userNavigation.find( "li.navItem" );

			// Attach clicks to menu items.
			userNavigation.find( "a.toggleUserSecondaryNavigation" ).click(
				function( event )
				{
					var listItem = $( event.target ).closest( "li" );

					// Prevent the default click.
					event.preventDefault();

					// Stop propagation of this click event.
					event.stopPropagation();

					// Toggle the secondary navigation
					userNavigationSecondaryNavigation.slideToggle( "fast" );

					// Toggle active state of clicked list item
					listItem.toggleClass( "activeMenuNavItem" );
				}
			);

			// Hide user secondary navigation when any other part of document is clicked.
			$(document).click(function(event)
			{
				if ( userNavigationSecondaryNavigation.is( ":visible" ) )
				{
					userNavigationSecondaryNavigation.hide();
					userNavigation.find( "li.activeMenuNavItem").removeClass( "activeMenuNavItem" );
				}
			});




AdjustHeightsForGoogleMap = {

    boxesSelector: "#GmapSideBarlist > li",

    adjustBoxes: function(){

        var currentTallest = 0;

        var currentRowStart = 0;

        var rowDivs = new Array();

        function setConformingHeight(el, newHeight) {
            // set the height to something new, but remember the original height in case things change
            el.data("originalHeight", (el.data("originalHeight") == undefined) ? (el.outerHeight()) : (el.data("originalHeight")));
            el.height(newHeight);
        }

        function getOriginalHeight(el) {
            // if the height has changed, send the originalHeight
            return (el.data("originalHeight") == undefined) ? (el.outerHeight()) : (el.data("originalHeight"));
        }

        function columnConform() {
         // find the tallest DIV in the row, and set the heights of all of the DIVs to match it.
            jQuery(AdjustHeightsForGoogleMap.boxesSelector).each(
                function(index) {

                    if(currentRowStart != jQuery(this).position().top) {

                        // we just came to a new row.  Set all the heights on the completed row
                        for(currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
                         setConformingHeight(rowDivs[currentDiv], currentTallest);
                        }

                        // set the variables for the new row
                        rowDivs.length = 0; // empty the array
                        currentRowStart = jQuery(this).position().top;
                        currentTallest = getOriginalHeight(jQuery(this));
                        rowDivs.push(jQuery(this));

                    }
                    else {

                     // another div on the current row.  Add it to the list and check if it's taller
                        rowDivs.push(jQuery(this));
                        currentTallest = (currentTallest < getOriginalHeight(jQuery(this))) ? (getOriginalHeight(jQuery(this))) : (currentTallest);

                    }
                    // do the last row
                    for(currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
                        setConformingHeight(rowDivs[currentDiv], currentTallest);
                    }
                }

            );
        }

        return columnConform;
    }
}

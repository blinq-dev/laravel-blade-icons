document.addEventListener("DOMContentLoaded", function() {
    const MAX_REQUESTS = 180;
    let observer;

    function loadSvgs(svgsInView) {
        // Split the SVGs into batches
        for (let i = 0; i < svgsInView.length; i += MAX_REQUESTS) {
            const batch = svgsInView.slice(i, i + MAX_REQUESTS);
            var svgData = batch.map(function(svg) {
                return btoa(svg.getAttribute('data-lazy'));
            }).join('|');

            // call /blinq-icons/lazy/{data}
            fetch('/blinq-icons/lazy/' + svgData)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    batch.forEach(function(svg, index) {
                        var attributes = svg.attributes;
                        let attributeString = "";
                        for (var j = 0; j < attributes.length; j++) {
                            if (attributes[j].name === 'data-lazy') continue;
                            attributeString += attributes[j].name + '="' + attributes[j].value + '" ';
                        }

                        // replace <svg by <svg {attributes}
                        var svgContent = data[index].replace('<svg', '<svg ' + attributeString);

                        // Set the outerHTML of the svg element to the data
                        svg.outerHTML = svgContent;
                    });
                });
        }
    }

    function observeSvgs() {
        if (observer) {
            observer.disconnect();
        }

        var lazySvg = document.querySelectorAll('svg[data-lazy]');
        var svgsInView = [];

        // Create a new IntersectionObserver instance
        observer = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    svgsInView.push(entry.target);
                    observer.unobserve(entry.target);
                }
            });

            if (svgsInView.length > 0) {
                loadSvgs(svgsInView);
                svgsInView = [];
            }
        });

        // Observe each lazy SVG element
        lazySvg.forEach(function(svg) {
            observer.observe(svg);
        });
    }

    observeSvgs();

    // Make the observeSvgs function globally accessible
    window.refreshLazySvgs = observeSvgs;




    // Function to handle mutations
    function handleMutations(mutationsList, mobserver) {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList' || mutation.type === 'attributes') {
                window.refreshLazySvgs(); // Refresh lazy SVGs if the DOM has changed
                return; // No need to check further if we've already refreshed
            }
        }
    }

    // Create an mobserver instance linked to the callback function
    const mobserver = new MutationObserver(handleMutations);

    // Start observing the entire document and its descendants
    mobserver.observe(document, { attributes: true, childList: true, subtree: true });

});

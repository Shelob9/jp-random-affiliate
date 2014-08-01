jQuery(document).ready(function($) {

    //sticky footer, via http://css-tricks.com/snippets/jquery/jquery-sticky-footer/
    $(window).bind("load", function() {

        var footerHeight = 0,
            footerTop = 0,
            $footer = $( "#jp_rand_aff" );

        positionFooter();

        function positionFooter() {

            footerHeight = $footer.height();
            footerTop = ($(window).scrollTop()+$(window).height()-footerHeight)+"px";


             console.log("Document height: ", $(document.body).height());
             console.log("Window height: ", $(window).height());
             console.log("Window scroll: ", $(window).scrollTop());
             console.log("Footer height: ", footerHeight);
             console.log("Footer top: ", footerTop);
             console.log("-----------")


            if ( ($(document.body).height()+footerHeight) < $(window).height()) {
                $footer.css({
                    position: "absolute"
                }).stop().animate({
                    top: footerTop
                })
            } else {
                $footer.css({
                    position: "static"
                })
            }

        }

        $(window)
            .scroll(positionFooter)
            .resize(positionFooter)

    });

});

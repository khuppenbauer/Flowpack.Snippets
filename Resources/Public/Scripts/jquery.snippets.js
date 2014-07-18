//submit form when filter has changed
$("select.jq-select").change(function() {
    $("input.jq-hidden").val('');
    this.form.submit();
});

//reset all filter and submit form
$(".jq-filter-reset").click(function() {
    $("select.jq-select").val('');
    $("input.jq-hidden").val('');
    $("#search").submit();
});

//set filter from links with data attribute
$('a').click(function(){
    var attributes = $(this).data();
    $.each(attributes, function(k,v) {
        if (k != 'currentpage') {
            $("select.jq-select").val('');
            $("input.jq-hidden").val('');
            $("input#query").val('');
        }
        $('#'+k).val(v);
        $("#search").submit();
    });
});

$(".jq-up").click(function (e) {
    sendRequest($(this), 'voteUp');
});

$(".jq-down").click(function (e) {
    sendRequest($(this), 'voteDown');
});

$(".jq-favor").click(function (e) {
    sendRequest($(this), 'favor');
});

function sendRequest(obj, action) {
    if (obj.data("post")) {
        $.ajax({
            type: 'POST',
            url: '/Post/' + action,
            data: {post: obj.data("post"), user: obj.data("user")},
            dataType: 'json',
            success: function (data) {
                $(".jq-upVotes").text(data.upVotes);
                $(".jq-downVotes").text(data.downVotes);
                if (data.favor) {
                    $(".jq-favor").removeClass("fa-star-o").addClass("fa-star");
                } else {
                    $(".jq-favor").removeClass("fa-star").addClass("fa-star-o");
                }
            }
        });
    } else {
        alert('Login to vote or favorite this post');
    }

}
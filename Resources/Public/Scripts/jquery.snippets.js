//reset all filter and submit form
$(".jq-filter-reset").click(function() {
    $("select.jq-select").val('');
    $("input.jq-hidden").val('');
    $("#search").submit();
});

//set filter from links with data attribute
$(document).on('click', 'a[data-currentPage], a[data-sortField], a[data-postType]', function(){
    var attributes = $(this).data();
    $.each(attributes, function(k,v) {
        $('#'+k).val(v);
        $("#search").submit();
    });
});

$(".jq-up").click(function (e) {
    e.preventDefault();
    sendRequest($(this), 'voteUp');
});

$(".jq-down").click(function (e) {
    e.preventDefault();
    sendRequest($(this), 'voteDown');
});

$(".jq-favor").click(function (e) {
    e.preventDefault();
    sendRequest($(this), 'favor');
});

$(".jq-follow-category").click(function (e) {
    e.preventDefault();
    sendFollowRequest('/Category/follow', 'category=' + $(this).data('category'), this);
});

$(".jq-follow-tag").click(function (e) {
    e.preventDefault();
    sendFollowRequest('/Tag/follow', 'tag=' + $(this).data('tag'), this);
});

$(".jq-follow-user").click(function (e) {
    e.preventDefault();
    sendFollowRequest('/User/follow', 'user=' + $(this).data('user'), this);
});

function sendFollowRequest(action, data, obj) {
    $.ajax({
        type: 'POST',
        url: action,
        data: data,
        dataType: 'json',
        context: obj,
        success: function (data) {
            if (data.followed === true) {
                $(this).removeClass('btn-default').addClass('btn theme');
                $(this).text('Following');
            } else {
                $(this).removeClass('btn theme').addClass('btn-default');
                $(this).text('Follow');
            }
            if(data.followers) {
                $("#jq-user-followers").text(data.followers);
            }
        }
    });
}

function sendRequest(obj, action) {
    if (obj.data("post")) {
        $.ajax({
            type: 'POST',
            url: '/Post/' + action,
            data: {post: obj.data('post')},
            dataType: 'json',
            success: function (data) {
                $('.jq-upVotes').text(data.upVotes);
                $('.jq-downVotes').text(data.downVotes);
                $('.jq-favorites').text(data.favorites);
                switchIcon('favor', data.favor, 'fa-star-o', 'fa-star');
                switchIcon('up', data.up, 'fa-thumbs-o-up', 'fa-thumbs-up');
                switchIcon('down', data.down, 'fa-thumbs-o-down', 'fa-thumbs-down');
            }
        });
    } else {
        $('#login').foundation('reveal', 'open', '');
    }
}

function switchIcon(type, active, inactiveIcon, activeIcon) {
    if (active === true) {
        $(".jq-" + type).removeClass(inactiveIcon).addClass(activeIcon);
    } else {
        $(".jq-" + type).removeClass(activeIcon).addClass(inactiveIcon);
    }
}
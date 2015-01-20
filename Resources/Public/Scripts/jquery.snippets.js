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
$(document).on('click', 'a[data-currentPage], a[data-sortField]', function(){
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
    $.ajax({
        type: 'POST',
        url: '/Category/follow',
        data: {category: $(this).data("category")},
        dataType: 'json',
        context: this,
        success: function (data) {
            $(this).toggleClass('secondary');
            $(this).text(data.followed);
        }
    });
});

$(".jq-follow-tag").click(function (e) {
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: '/Tag/follow',
        data: {tag: $(this).data("tag")},
        dataType: 'json',
        context: this,
        success: function (data) {
            $(this).toggleClass('secondary');
            $(this).text(data.followed);
        }
    });
});

$(".jq-follow-user").click(function (e) {
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: '/User/follow',
        data: {user: $(this).data("user")},
        dataType: 'json',
        context: this,
        success: function (data) {
            $(this).toggleClass('secondary');
            $(this).text(data.followed);
        }
    });
});

function sendRequest(obj, action) {
    if (obj.data("post")) {
        $.ajax({
            type: 'POST',
            url: '/Post/' + action,
            data: {post: obj.data("post")},
            dataType: 'json',
            success: function (data) {
                $(".jq-upVotes").text(data.upVotes);
                $(".jq-downVotes").text(data.downVotes);
                $(".jq-favorites").text(data.favorites);
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
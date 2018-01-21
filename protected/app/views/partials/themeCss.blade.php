@if(!empty($mainBtnColor))
    .btn-primary {
        background-color: transparent;
    }
    .btn-primary span{
        background-color: {{$mainBtnColor}};
    }
    .grid-box .boxed.question-choice:hover {
        background: {{$mainBtnColor}};
        color: #fff;
        border-color: transparent;
    }
@endif

@if(!empty($linkColor))
    a{
        color: {{$linkColor}};
    }
    a:hover, a:focus {
        color: #222;
        text-decoration: none;
    }
@endif

#topmenu .dropdown {
    background-color: {{$navbarColor}};
}

#topmenu .dropdown > li{
    border-right: 1px solid rgba(255, 255, 255, 0.24);
}

#topmenu .dropdown > li a{
    color: #ffffff;
}

#topmenu #headerUserLoginLink > span {
    background: #fff;
    color: #222;
}

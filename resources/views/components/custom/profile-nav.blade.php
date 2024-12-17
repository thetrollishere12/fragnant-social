<div id="profile-nav-button" class="py-2 pl-2 border-b border-gray-200 block md:hidden cursor-pointer hover:bg-gray-100 bg-white"><span class="icon-list"></span></div>

<div id="sidebar-option" class="bg-white md:min-h-screen w-full md:w-96 hidden md:block text-current z-1 relative border-b md:border-b-0 border-gray-200 text-lg">
    
    <div class="px-2 py-1">
        <div class="rounded transition p-3 text-sm">ACCOUNT</div>
    </div>

    <div class="px-3 py-1">
        <a href="{{ url('user/profile') }}"><div class="@if(isset($url) && $url == 'profile') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-user pr-3"></span> Profile</div></a>
    </div>


    <div class="px-3 py-1">
        <a href="{{ url('user/subscription') }}"><div class="@if(isset($url) && $url == 'subscription') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-table pr-3"></span> Subscription</div></a>
    </div>


    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())











    <div class="px-2 py-1">
        <div class="rounded transition p-3 text-sm">TEAMS</div>
    </div>

    @if(Auth::user()->ownedTeams->count() > 0)

    <div class="px-3 py-1">
        <a href="{{ url('teams') }}"><div class="@if(isset($url) && $url == 'teams') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-user pr-3"></span>View Teams</div></a>
    </div>


    <div class="px-3 py-1">
        <a href="{{ url('team/assignment') }}"><div class="@if(isset($url) && $url == 'team assignment') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-user pr-3"></span>Team Assignment</div></a>
    </div>


 <!--    <div class="px-3 py-1">
        <a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}"><div class="@if(isset($url) && $url == 'team settings') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-user pr-3"></span> Team Settings</div></a>
    </div> -->

    

    @else
    <div class="px-3 py-1">
        <a href="{{ route('teams.create') }}"><div class="@if(isset($url) && $url == 'create team') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-user pr-3"></span> Create Team</div></a>
    </div>
    @endif











    @if(Auth::user()->teams->count() > 0)

    <div class="px-2 py-1">
        <div class="rounded transition p-3 text-sm">TEAM PROJECTS</div>
    </div>


    @foreach(Auth::user()->teams as $team)

    <div class="px-3 py-1">
        <a href="{{ route('team-project.index', $team) }}"><div class="@if(isset($url) && $url == (string)$team->id) main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-office pr-3"></span>{{ $team->name }}</div></a>
    </div>

    @endforeach

    @endif







    @endif




    <div class="px-2 py-1">
        <div class="rounded transition p-3 text-sm">ASSETS</div>
    </div>



    <div class="px-3 py-1">
        <a href="{{ url('user/digital-assets') }}"><div class="@if(isset($url) && $url == 'digital-assets') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-pencil pr-3"></span> My Digital Assets</div></a>
    </div>



  <!--   <div class="px-3 py-1">
        <a href="{{ url('user/reusable-block') }}"><div class="@if(isset($url) && $url == 'reusable-block') main-bg-c text-white @else hover:bg-gray-100 @endif rounded transition p-3 text-sm"><span class="icon-tree pr-3"></span> Reusable Block</div></a>
    </div> -->

    

</div>

<script type="text/javascript">
    
    $('#profile-nav-button').click(function(){
        $('#sidebar-option').slideToggle();
    });

</script>
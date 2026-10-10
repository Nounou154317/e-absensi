@if(session('success'))
    <div class="alert alert-success py-2 mb-3" style="font-size: 13px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger py-2 mb-3" style="font-size: 13px;">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="font-size: 13px;">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
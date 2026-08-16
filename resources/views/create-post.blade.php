<x-layout>
  <div class="container py-md-5 container--narrow">
      <form action="/create-post" method="POST">
        @csrf
        <div class="form-group">
          <label for="post-title" class="text-muted mb-1"><small>Title</small></label>
          <input required name="title" id="post-title" class="form-control form-control-lg form-control-title" type="text" placeholder="" autocomplete="off" />
          @error('title')
            <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
          @enderror
        </div>

        <div class="form-group">
          <label for="post-body" class="text-muted mb-1"><small>Body Content</small></label>
          <textarea required name="body" id="post-body" class="body-content tall-textarea form-control" type="text"></textarea>
          @error('body')
            <p class="m-0 small alert alert-danger shadow-sm">{{$message}}</p>
           @enderror
        </div>

        <button class="btn btn-primary">Save New Post</button>
      </form>
    </div>
</x-layout>
<script>  
      $(document).ready(function() {
          $('#post-body').summernote({
               placeholder: 'Enter your post here...',
                tabsize: 2,
                height: 300,
                toolbar: [
                  ['style', ['style']],
                  ['font', ['bold', 'underline', 'clear']],
                  ['color', ['color']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['table', ['table']],
                  ['view', ['fullscreen', 'codeview', 'help']]
                ]

          });
      });
    </script>

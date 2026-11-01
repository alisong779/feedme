<x-layout pagetitle="Create New Post">
  <div class="container py-md-5 container--narrow">
     <livewire:create-post />
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

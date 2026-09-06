<x-profile :sharedData="$sharedData" :pagetitle="$pagetitle" :hide_author="true">
  <div class="list-group">
        @foreach($posts as $post)
         <x-post :post="$post" />
        @endforeach
      </div>
</x-profile>
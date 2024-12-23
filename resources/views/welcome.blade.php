<x-app-layout>
    <style>
        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: black; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typingElement = document.getElementById('typing');
            const texts = ["old blogs into engaging stories.", "long articles into short reels.", "content into a fresh perspective."];
            let currentText = "";
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;

            function type() {
                if (isDeleting) {
                    currentText = texts[textIndex].substring(0, charIndex--);
                } else {
                    currentText = texts[textIndex].substring(0, charIndex++);
                }

                typingElement.textContent = currentText;

                if (!isDeleting && charIndex === texts[textIndex].length) {
                    isDeleting = true;
                    setTimeout(type, 500);
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % texts.length;
                    setTimeout(type, 250);
                } else {
                    setTimeout(type, isDeleting ? 25 : 50);
                }
            }

            type();
        });
    </script>

    <div class="px-4 pt-8 pb-20">
        <div class="max-w-7xl mx-auto grid md:grid-cols-1 gap-6">
            <div>
                <div class="py-8">
                    <div class="flex gap-4 items-center justify-center">
                        <h1 class="font-bold text-7xl text-center main-t-c">Transform Old Content</h1>
                    </div>

                   

                    <p class="text-center max-w-3xl pt-4 pb-14 mx-auto text-lg">
                        Breathe new life into your existing content. Repurpose blogs, articles, or ideas into modern, engaging formats like short reels, scripts, or interactive stories.
                    </p>
                </div>

                <h3 class="p-2 font-bold text-4xl mb-2">What Will You Transform Today?</h3>
                <div class="border rounded-lg bg-gray-100 py-3 px-4 bg-white text-xl tracking-widest">
                    Turn <span id="typing"></span>
                </div>
            </div>

            <div>
                <div class="grid md:grid-cols-2 gap-3">
                    <div class="w-full">
                        <div class="font-bold p-2 text-xl">Your Old, Unused Content</div>
                                                
                        <div class="grid grid-cols-3 gap-4">
                            @for ($i = 1; $i <= 9; $i++)
                                <div class="rounded-lg w-full
                                            h-40 overflow-hidden">
                                    <img 
                                        class="
                                            transition duration-150 ease-in-out
                                            object-cover
                                        "
                                        src="{{ Storage::disk('public')->url('image/welcome/' . $i . '.jpg') }}" 
                                        alt="Image {{ $i }}"
                                    />
                                </div>
                            @endfor
                        </div>

                    </div>

                    <div>
                        <div class="font-bold p-2 text-xl">Auto Brand New Content!</div>
                        <video class="w-full max-w-5xl mx-auto" controls>
                            <source src="{{ Storage::disk('public')->url('image/welcome/sample_2.mp4') }}" type="video/mp4">
                        </video>
                        <a href="{{ url('user/digital-assets') }}">


                            @livewire('admin.trigger-event-button', [
                                'className' => 'text-xl mt-2 w-full main-bg-c text-white rounded py-2.5',
                                'text' => 'Start Repurposing Now',
                                'subject' => 'Repurpose Button Clicked',
                                'message' => 'This is a message to let you know this button was clicked'
                            ])



                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-28 main-bg-c px-4">
        <div class="text-white">
            <h1 class="text-center font-bold text-5xl">Give Old Content a New Life</h1>
            <p class="text-center text-xl py-8 max-w-5xl mx-auto">
                Don't let great content fade away. Helps you reimagine and repurpose your existing assets, transforming them into fresh, impactful formats for your audience.
            </p>
        </div>

        <div class="grid px-4 gap-4 md:gap-14 md:grid-cols-3 max-w-5xl mx-auto py-20">
            <div class="bg-white rounded-xl p-8">
           
                <h2 class="font-bold pb-4">Create Video Reels</h2>
                <p class="text-sm">Turn blog highlights into short, engaging video reels for social media.</p>
            </div>

            <div class="bg-white rounded-xl p-8">
             
                <h2 class="font-bold pb-4">Generate Infographics</h2>
                <p class="text-sm">Transform articles into visually appealing infographics for better reach.</p>
            </div>

            <div class="bg-white rounded-xl p-8">
             
                <h2 class="font-bold pb-4">Craft Interactive Stories</h2>
                <p class="text-sm">Bring depth to old content with interactive scripts and storytelling.</p>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ url('user/media') }}">

                @livewire('admin.trigger-event-button', [
                    'className' => 'text-xl bg-indigo-500 text-white rounded px-4 py-2.5',
                    'text' => 'Get Started',
                    'subject' => 'Get Started Button Clicked',
                    'message' => 'This is a message to let you know this button was clicked'
                ])



            </a>
        </div>
    </div>
</x-app-layout>
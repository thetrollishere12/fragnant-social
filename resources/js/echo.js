import Echo from "laravel-echo";
import Pusher from "pusher-js";

console.log("echo.js is loaded!"); // Debug log to verify it’s included

console.log(import.meta.env.VITE_REVERB_HOST); // Should log "fragnant.com"
console.log(import.meta.env.VITE_REVERB_PORT); // Should log "8080"

console.log("bra is loaded!"); // Debug log to verify it’s included

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});



// Test listener
window.Echo.channel("test-reverb-channel").listen(".TestReverbEvent", (e) => {
    console.log("Reverb Message:", e.message);
});




window.Echo.private(`user.${window.userId}`)

    .listen('.media-processed', () => {
        console.log('Media has been processed!');
        window.Livewire.dispatch('mediaProcessed');
    })

    .listen('.subscription-status', (e) => {
        console.log(e.message);
        $wireui.dialog({
            title: e.title,
            description: e.message,
            icon: 'error',
            accept: {
                label: 'Upgrade',
                url: '/subscription-pricing'
            },
        });
    })

    .listen('.publish.processing', (e) => {
        console.log(`Stage: ${e.stage}, Message: ${e.message}, Progress: ${e.progress}%`);
        
        // Update Livewire component
        window.Livewire.dispatch('updateStage', { 
            stage: e.stage, 
            message: e.message, 
            progress: e.progress 
        });
    });
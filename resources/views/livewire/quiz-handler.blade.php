<script src="https://cdn.tailwindcss.com"></script>
<div 
    x-data="{
        time: @entangle('duration').defer,
        countdown: null,
        startCountdown() {
            this.countdown = setInterval(() => {
                if (this.time > 0) {
                    this.time--;
                } else {
                    clearInterval(this.countdown);
                    $wire.nextQuestion();
                }
            }, 1000);
        },
        resetTimer(duration) {
            this.time = duration;
            clearInterval(this.countdown);
            this.startCountdown();
        }
    }"
    x-init="startCountdown()"
    @reset-timer.window="resetTimer($event.detail.duration)"
>
    <!-- HTML quiz kamu -->
</div>

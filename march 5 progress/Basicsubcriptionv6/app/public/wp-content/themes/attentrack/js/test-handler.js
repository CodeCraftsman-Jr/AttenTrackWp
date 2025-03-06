class AttentionTest {
    constructor(config) {
        this.config = {
            duration: config.duration || 300, // 5 minutes in seconds
            stimuliDuration: config.stimuliDuration || 2000, // 2 seconds
            interStimulusInterval: config.interStimulusInterval || 1000, // 1 second
            stimuli: config.stimuli || [],
            onComplete: config.onComplete || function() {},
            onProgress: config.onProgress || function() {}
        };
        
        this.state = {
            isRunning: false,
            isPaused: false,
            startTime: null,
            elapsedTime: 0,
            currentStimulusIndex: 0,
            responses: [],
            timer: null
        };
    }

    start() {
        this.state.isRunning = true;
        this.state.startTime = Date.now() - this.state.elapsedTime;
        this.state.timer = setInterval(() => this.updateTimer(), 1000);
        this.presentNextStimulus();
    }

    pause() {
        this.state.isPaused = true;
        clearInterval(this.state.timer);
    }

    resume() {
        this.state.isPaused = false;
        this.state.startTime = Date.now() - this.state.elapsedTime;
        this.state.timer = setInterval(() => this.updateTimer(), 1000);
    }

    stop() {
        this.state.isRunning = false;
        clearInterval(this.state.timer);
        this.config.onComplete(this.getResults());
    }

    updateTimer() {
        if (!this.state.isPaused) {
            this.state.elapsedTime = Date.now() - this.state.startTime;
            const seconds = Math.floor(this.state.elapsedTime / 1000);
            
            if (seconds >= this.config.duration) {
                this.stop();
            } else {
                this.config.onProgress({
                    elapsedTime: this.state.elapsedTime,
                    remainingTime: this.config.duration * 1000 - this.state.elapsedTime
                });
            }
        }
    }

    presentNextStimulus() {
        if (!this.state.isRunning || this.state.isPaused) return;

        const stimulus = this.config.stimuli[this.state.currentStimulusIndex];
        const presentationTime = Date.now();

        // Present stimulus
        this.config.onProgress({
            type: 'stimulus',
            stimulus: stimulus,
            index: this.state.currentStimulusIndex
        });

        // Schedule stimulus removal
        setTimeout(() => {
            if (this.state.isRunning && !this.state.isPaused) {
                this.config.onProgress({
                    type: 'clear'
                });

                // Schedule next stimulus
                setTimeout(() => {
                    if (this.state.isRunning && !this.state.isPaused) {
                        this.state.currentStimulusIndex++;
                        if (this.state.currentStimulusIndex < this.config.stimuli.length) {
                            this.presentNextStimulus();
                        }
                    }
                }, this.config.interStimulusInterval);
            }
        }, this.config.stimuliDuration);
    }

    recordResponse(response) {
        if (!this.state.isRunning || this.state.isPaused) return;

        this.state.responses.push({
            stimulusIndex: this.state.currentStimulusIndex,
            response: response,
            timestamp: Date.now()
        });
    }

    getResults() {
        const totalResponses = this.state.responses.length;
        const correctResponses = this.state.responses.filter(r => r.response.correct).length;
        const accuracy = totalResponses > 0 ? (correctResponses / totalResponses) * 100 : 0;

        const responseTimes = this.state.responses.map(r => r.response.responseTime);
        const averageResponseTime = responseTimes.length > 0 
            ? responseTimes.reduce((a, b) => a + b) / responseTimes.length 
            : 0;

        return {
            duration: this.state.elapsedTime / 1000,
            totalStimuli: this.config.stimuli.length,
            responsesGiven: totalResponses,
            correctResponses: correctResponses,
            accuracy: accuracy,
            averageResponseTime: averageResponseTime,
            responses: this.state.responses
        };
    }
}

// Stimulus generators
const StimulusGenerators = {
    shapes: function(config) {
        const shapes = config.shapes || ['circle', 'square', 'triangle'];
        const colors = config.colors || ['#ff0000', '#00ff00', '#0000ff'];
        
        return shapes.flatMap(shape => 
            colors.map(color => ({
                type: 'shape',
                shape: shape,
                color: color,
                target: config.targets ? config.targets.includes(shape) : false
            }))
        );
    },

    letters: function(config) {
        const letters = config.letters || 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        return letters.map(letter => ({
            type: 'letter',
            value: letter,
            target: config.targets ? config.targets.includes(letter) : false
        }));
    },

    numbers: function(config) {
        const range = config.range || [0, 9];
        const numbers = Array.from(
            {length: range[1] - range[0] + 1}, 
            (_, i) => i + range[0]
        );
        
        return numbers.map(number => ({
            type: 'number',
            value: number,
            target: config.targets ? config.targets.includes(number) : false
        }));
    }
};

// Export for use in WordPress
window.AttentionTest = AttentionTest;
window.StimulusGenerators = StimulusGenerators;

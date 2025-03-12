class TestPhase {
    constructor(config) {
        this.title = config.title;
        this.duration = config.duration;
        this.stimuli = config.stimuli;
        this.type = config.type;
        this.responses = [];
        this.startTime = null;
        this.endTime = null;
    }

    start() {
        this.startTime = performance.now();
    }

    end() {
        this.endTime = performance.now();
        return {
            duration: this.endTime - this.startTime,
            responses: this.responses
        };
    }

    recordResponse(stimulus, response, responseTime) {
        this.responses.push({
            stimulus,
            response,
            responseTime,
            timestamp: performance.now()
        });
    }

    calculateAccuracy() {
        if (this.responses.length === 0) return 0;
        const correct = this.responses.filter(r => r.stimulus === r.response).length;
        return (correct / this.responses.length) * 100;
    }

    calculateAverageResponseTime() {
        if (this.responses.length === 0) return 0;
        const total = this.responses.reduce((sum, r) => sum + r.responseTime, 0);
        return total / this.responses.length;
    }
}

class AttentionTest {
    constructor(config) {
        this.config = config;
        this.phases = [];
        this.currentPhaseIndex = -1;
        this.results = [];
        this.startTime = null;
        this.endTime = null;
        this.isPaused = false;
        this.events = {};
    }

    initialize() {
        this.config.phases.forEach(phaseConfig => {
            this.phases.push(new TestPhase(phaseConfig));
        });
    }

    start() {
        this.startTime = performance.now();
        this.nextPhase();
    }

    pause() {
        this.isPaused = true;
        this.emit('paused');
    }

    resume() {
        this.isPaused = false;
        this.emit('resumed');
    }

    nextPhase() {
        if (this.currentPhaseIndex >= 0) {
            const currentPhase = this.phases[this.currentPhaseIndex];
            this.results.push(currentPhase.end());
        }

        this.currentPhaseIndex++;
        if (this.currentPhaseIndex < this.phases.length) {
            const newPhase = this.phases[this.currentPhaseIndex];
            newPhase.start();
            this.emit('phaseStart', newPhase);
        } else {
            this.end();
        }
    }

    end() {
        this.endTime = performance.now();
        const finalResults = this.calculateResults();
        this.emit('testComplete', finalResults);
    }

    calculateResults() {
        const totalDuration = this.endTime - this.startTime;
        const phaseResults = this.phases.map((phase, index) => ({
            phaseNumber: index + 1,
            title: phase.title,
            accuracy: phase.calculateAccuracy(),
            averageResponseTime: phase.calculateAverageResponseTime(),
            responses: phase.responses
        }));

        const overallAccuracy = phaseResults.reduce((sum, phase) => sum + phase.accuracy, 0) / phaseResults.length;
        const overallResponseTime = phaseResults.reduce((sum, phase) => sum + phase.averageResponseTime, 0) / phaseResults.length;

        return {
            totalDuration,
            overallAccuracy,
            overallResponseTime,
            phaseResults
        };
    }

    on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
    }

    emit(event, data) {
        if (this.events[event]) {
            this.events[event].forEach(callback => callback(data));
        }
    }
}

class StimulusPresenter {
    constructor(container) {
        this.container = container;
        this.currentStimulus = null;
        this.startTime = null;
    }

    present(stimulus) {
        this.clear();
        this.currentStimulus = stimulus;
        this.startTime = performance.now();

        switch (stimulus.type) {
            case 'text':
                this.presentText(stimulus);
                break;
            case 'image':
                this.presentImage(stimulus);
                break;
            case 'pattern':
                this.presentPattern(stimulus);
                break;
        }
    }

    presentText(stimulus) {
        const element = document.createElement('div');
        element.className = 'stimulus-text';
        element.textContent = stimulus.content;
        this.container.appendChild(element);
    }

    presentImage(stimulus) {
        const element = document.createElement('img');
        element.className = 'stimulus-image';
        element.src = stimulus.content;
        element.alt = stimulus.alt || '';
        this.container.appendChild(element);
    }

    presentPattern(stimulus) {
        const element = document.createElement('div');
        element.className = 'stimulus-pattern';
        element.innerHTML = stimulus.content;
        this.container.appendChild(element);
    }

    clear() {
        this.container.innerHTML = '';
        this.currentStimulus = null;
        this.startTime = null;
    }

    getResponseTime() {
        return this.startTime ? performance.now() - this.startTime : null;
    }
}

class ResponseCollector {
    constructor() {
        this.callbacks = {};
        this.activeListeners = new Set();
    }

    startCollecting(type, callback) {
        this.stopCollecting(); // Remove any existing listeners

        switch (type) {
            case 'keyboard':
                this.collectKeyboardResponse(callback);
                break;
            case 'mouse':
                this.collectMouseResponse(callback);
                break;
            case 'touch':
                this.collectTouchResponse(callback);
                break;
        }
    }

    collectKeyboardResponse(callback) {
        const handler = (event) => {
            callback({
                type: 'keyboard',
                key: event.key,
                timestamp: performance.now()
            });
        };
        document.addEventListener('keydown', handler);
        this.activeListeners.add({ type: 'keydown', handler });
    }

    collectMouseResponse(callback) {
        const handler = (event) => {
            callback({
                type: 'mouse',
                x: event.clientX,
                y: event.clientY,
                timestamp: performance.now()
            });
        };
        document.addEventListener('click', handler);
        this.activeListeners.add({ type: 'click', handler });
    }

    collectTouchResponse(callback) {
        const handler = (event) => {
            const touch = event.touches[0];
            callback({
                type: 'touch',
                x: touch.clientX,
                y: touch.clientY,
                timestamp: performance.now()
            });
        };
        document.addEventListener('touchstart', handler);
        this.activeListeners.add({ type: 'touchstart', handler });
    }

    stopCollecting() {
        this.activeListeners.forEach(listener => {
            document.removeEventListener(listener.type, listener.handler);
        });
        this.activeListeners.clear();
    }
}

// Export the classes for use in other files
export { AttentionTest, TestPhase, StimulusPresenter, ResponseCollector };

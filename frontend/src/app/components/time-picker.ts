import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop} from "vue-property-decorator";
import $ from "jquery";
import * as moment from "moment";

/**
 * Компонент выбора времени
 */
@Component({
    template: require('./time-picker.html')
})
export class TimePicker extends Vue {
    @Prop(String) time: string;

    @Prop(String) minTime: string;

    @Prop(String) maxTime: string;

    @Model() model: string = this.time ? this.time : (
        this.minTime ? this.minTime : '00:00'
    );

    mounted(): void {
        let timePickerOptions: TimePickerOptions = <TimePickerOptions>{
            step: 30,
            timeFormat: 'H:i',
            forceRoundTime: true,
            minTime: this.minTime ? this.minTime : '00:00',
            maxTime: this.maxTime ? this.maxTime : '23:59'
        };
        let ref = this.$refs['button'];
        $(ref).timepicker(timePickerOptions);
        $(ref).on('selectTime', () => {
            let date = $(ref).timepicker('getTime');
            this.model = moment(date).format('HH:mm');

            this.$emit('set-time', this.model);
        });
    }
}

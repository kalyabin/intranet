import Vue from "vue";
import Component from "vue-class-component";
import {Model, Prop, Watch} from "vue-property-decorator";
import $ from "jquery";
import * as moment from "moment";
import {RoomInterface} from "../service/model/room.interface";
import {roomRequestHelper} from "../helpers/room-request-helper";

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

    @Prop(Object) room: RoomInterface;

    @Prop(String) date: string;

    @Prop(Boolean) isFrom: boolean;

    @Prop() unavailable: Array<string[]>;

    @Model() model: string = this.time ? this.time : '--:--';

    @Watch('minTime')
    onChangeMinTime(newVal: string): void {
        let ref = this.$refs['button'];
        if (newVal) {
            $(ref).timepicker('option', {'minTime': newVal});
        }
    }

    @Watch('maxTime')
    onChangeMaxTIme(newVal: string): void {
        let ref = this.$refs['button'];
        if (newVal) {
            $(ref).timepicker('option', {'maxTime': newVal});
        }
    }

    mounted(): void {
        let timePickerOptions: TimePickerOptions = <TimePickerOptions>{
            step: 30,
            timeFormat: 'H:i',
            forceRoundTime: true,
        };
        if (this.maxTime) {
            timePickerOptions['maxTime'] = this.maxTime;
        }
        if (this.minTime) {
            timePickerOptions['minTime'] = this.minTime;
        }
        if (this.unavailable) {
            timePickerOptions['disableTimeRanges'] = this.unavailable;
        } else if (this.room && this.date) {
            timePickerOptions['disableTimeRanges'] = this.isFrom ?
                roomRequestHelper.getDisabledTimeFromRanges(this.room, this.date) :
                roomRequestHelper.getDisabledTimeToRanges(this.room, this.date);
        }
        let ref = this.$refs['button'];
        $(ref).timepicker(timePickerOptions);
        $(ref).on('selectTime', () => {
            let date = $(ref).timepicker('getTime');
            this.model = moment(date).format('HH:mm');
            this.$emit('set-time', this.model);
        });
    }
}

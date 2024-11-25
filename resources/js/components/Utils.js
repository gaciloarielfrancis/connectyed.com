import { format } from 'date-fns';

export function  getTimeZones() {
    return Intl.supportedValuesOf('timeZone');
}

export function formatDate (datetime, toFormat = "yyyy-MM-dd") {
    return format(new Date(datetime), toFormat);
}

export function formatTime (datetime, toFormat = "H:mm:ss") {
    return format(new Date(datetime), toFormat);
}

export function formatDateTime (datetime, toFormat = "yyyy-MM-dd H:mm:ss") {
    return format(new Date(datetime), toFormat);
}

export function parseAvailabilities(availabilities = []) {
    const availableDates = [];
    const availableDateTime = [];
    availabilities.forEach(data => {
        const nowDate = new Date();
        const startTime = data.is_all_day ? '00:00' : data.start_time;
        const endTime = data.is_all_day ? '24:00' : data.end_time;
        let startDate = new Date(data.start_date + ' ' + startTime);
        const endDate = new Date(data.end_date + ' ' + endTime);

        if(nowDate > startDate) startDate = new Date();

        const diffInMs = endDate.getTime() - startDate.getTime();
        const diffInDays = parseInt(diffInMs / (1000 * 60 * 60 * 24)) + 1;
        
        const diffInMsNow = new Date(data.end_date + ' ' + endTime).getTime() - new Date(data.end_date + ' ' + startTime).getTime();
        const diffInHours = parseInt(diffInMsNow / (1000 * 60 * 60));

        for(let i = 0; i < diffInDays; i++) {
            const dateAdded = new Date(startDate);
            let timeAdded = new Date(formatDate(dateAdded) + ' ' + startTime);
            availableDates.push(dateAdded);
            for(let x = 0; x < (diffInHours * 2) + 1; x++) {
                const dateTime = new Date(timeAdded);
                if(dateTime > nowDate && endDate > dateTime) {
                    availableDateTime.push(dateTime);
                }
                timeAdded.setMinutes(timeAdded.getMinutes() + 30);
            }
            startDate.setDate(startDate.getDate() + 1);
        }
    });
    return {
        availableDates: availableDates,
        availableDateTime: availableDateTime
    }
}
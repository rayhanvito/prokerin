import { Clock3 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface CountdownTimerProps {
    targetDate: string | null;
}

interface TimeLeft {
    days: number;
    hours: number;
    minutes: number;
}

export default function CountdownTimer({ targetDate }: CountdownTimerProps) {
    const target = useMemo(
        () => (targetDate === null ? null : new Date(targetDate)),
        [targetDate],
    );
    const [timeLeft, setTimeLeft] = useState<TimeLeft>(() =>
        calculateTimeLeft(target),
    );

    useEffect(() => {
        const interval = window.setInterval(() => {
            setTimeLeft(calculateTimeLeft(target));
        }, 60000);

        setTimeLeft(calculateTimeLeft(target));

        return () => window.clearInterval(interval);
    }, [target]);

    if (target === null || Number.isNaN(target.getTime())) {
        return null;
    }

    return (
        <div className="inline-flex items-center gap-3 rounded-[4px] bg-white px-4 py-3 text-[#242934] shadow-sm ring-1 ring-[#e6edef]">
            <span className="flex h-9 w-9 items-center justify-center rounded-[4px] bg-[#24695c] text-white">
                <Clock3 className="h-4 w-4" />
            </span>
            <div>
                <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#717171]">
                    Menuju Event
                </p>
                <p className="text-sm font-semibold text-[#242934]">
                    {timeLeft.days}h {timeLeft.hours}j {timeLeft.minutes}m
                </p>
            </div>
        </div>
    );
}

function calculateTimeLeft(target: Date | null): TimeLeft {
    if (target === null || Number.isNaN(target.getTime())) {
        return { days: 0, hours: 0, minutes: 0 };
    }

    const difference = Math.max(0, target.getTime() - Date.now());
    const totalMinutes = Math.floor(difference / 60000);
    const days = Math.floor(totalMinutes / 1440);
    const hours = Math.floor((totalMinutes % 1440) / 60);
    const minutes = totalMinutes % 60;

    return { days, hours, minutes };
}

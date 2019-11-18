package com.stbemanning.notifications;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.app.TaskStackBuilder;
import android.content.Context;
import android.content.Intent;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.media.RingtoneManager;
import android.support.v4.app.NotificationCompat;
import com.stbemanning.R;
import com.stbemanning.activities.CalendarActivity;
import com.stbemanning.activities.SpecialWorkShiftActivity;

public class MyNotificationManager {

//    public static final int ID_BIG_NOTIFICATION = 234;
    public static final int ID_SMALL_NOTIFICATION = 235;

    private Context mCtx;

    public MyNotificationManager(Context mCtx) {
        this.mCtx = mCtx;
    }

    //the method will show a small notification
    //parameters are title for message title, message for message text and an intent that will open
    //when you will tap on the notification
    public void showSmallNotification(String title, String message, Intent intent) {

        TaskStackBuilder stackBuilder = TaskStackBuilder.create(mCtx);
        stackBuilder.addParentStack(CalendarActivity.class);
        stackBuilder.addNextIntent(intent);
        Intent intentSpecialWorkShifts= new Intent (mCtx, SpecialWorkShiftActivity.class);
//            intentSpecialWorkShifts.putExtra("EmailId","you can Pass emailId here");
        stackBuilder.addNextIntent(intentSpecialWorkShifts);
        PendingIntent pendingIntent = stackBuilder.getPendingIntent(0, PendingIntent.FLAG_UPDATE_CURRENT);

        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(mCtx);
        Notification notification;
        notification = mBuilder.setSmallIcon(R.mipmap.stsmall).setTicker(title).setWhen(0)
                .setAutoCancel(true)
                .setContentIntent(pendingIntent)
                .setLights(Color.BLUE, 500, 500)
                .setSound(RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION))
                .setContentTitle(title)
                .setSmallIcon(R.mipmap.stsmall)
                .setLargeIcon(BitmapFactory.decodeResource(mCtx.getResources(), R.mipmap.stsmall))
                .setContentText(message)
                .build();

        notification.flags |= Notification.FLAG_AUTO_CANCEL;

        NotificationManager notificationManager = (NotificationManager) mCtx.getSystemService(Context.NOTIFICATION_SERVICE);
        notificationManager.notify(ID_SMALL_NOTIFICATION, notification);
    }
}

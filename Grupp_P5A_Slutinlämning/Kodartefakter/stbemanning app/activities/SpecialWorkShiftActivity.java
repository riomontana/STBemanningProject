package com.stbemanning.activities;

import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.os.Handler;
import android.preference.PreferenceManager;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.AdapterView;
import android.widget.LinearLayout;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import com.stbemanning.R;
import com.stbemanning.api.Api;
import com.stbemanning.api.ApiListener;
import com.stbemanning.api.PerformNetworkRequest;
import com.stbemanning.model.User;
import com.stbemanning.model.WorkShift;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class SpecialWorkShiftActivity extends AppCompatActivity implements ApiListener {

    private User user;
    private List<WorkShift> specialWorkShiftList;
    private List<WorkShift> tempSpecialWorkShiftList;
    private ListView lvSpecialWorkShifts;
    private LinearLayout linearLayout;
    private SharedPreferences prefs;
    private int userId;
    private int mInterval = 1000;
    private Handler mHandler;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_special_work_shift);
        prefs = PreferenceManager.getDefaultSharedPreferences(this);
        userId = prefs.getInt("USER_ID", 0);
        tempSpecialWorkShiftList = new ArrayList<>();
        getSpecialWorkShifts(userId);
//        user = (User) getIntent().getSerializableExtra("user");
//        getSpecialWorkShifts(user.getUserId());
        lvSpecialWorkShifts = findViewById(R.id.lvSpecialWorkShifts);
        linearLayout = findViewById(R.id.linearLayout);
        clickListHandler();
        mHandler = new Handler();
        startRepeatingTask();
    }

    private void startRepeatingTask() {
        mStatusChecker.run();
    }

    private void stopRepeatingTask() {
        mHandler.removeCallbacks(mStatusChecker);
    }

    Runnable mStatusChecker = new Runnable() {
        @Override
        public void run() {
            try {
                getSpecialWorkShifts(userId);
            } finally {
                mHandler.postDelayed(mStatusChecker, mInterval);
            }
        }
    };

    private void clickListHandler() {
        lvSpecialWorkShifts.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> adapterView, View view, int pos, long id) {
                final WorkShift specialWorkShift = specialWorkShiftList.get(pos);
                String[] splitDateTimeStart = specialWorkShift.getShiftStart().split(" ");
                String[] splitDateTimeEnd = specialWorkShift.getShiftEnd().split(" ");
                AlertDialog alertDialog = new AlertDialog.Builder(SpecialWorkShiftActivity.this).create();
                alertDialog.setTitle("Vill du tacka ja till det här specialpasset?");
                alertDialog.setIcon(R.mipmap.stsmall);
                alertDialog.setMessage(specialWorkShift.getCompany() + "\nDatum: " + splitDateTimeStart[0] +
                        "\nTid: " + splitDateTimeStart[1] + " - " + splitDateTimeEnd[1]);
                alertDialog.setButton(AlertDialog.BUTTON_POSITIVE, "TACKA JA",
                        new DialogInterface.OnClickListener() {
                            public void onClick(DialogInterface dialog, int which) {
                                updateWorkShift(specialWorkShift);
                                Toast.makeText(getBaseContext(),
                                        "Du tackade ja till arbetspasset och det har nu lagts till i din kalender",
                                        Toast.LENGTH_SHORT).show();
                                getSpecialWorkShifts(userId);
                            }
                        });
                alertDialog.setButton(AlertDialog.BUTTON_NEGATIVE, "AVBRYT",
                        new DialogInterface.OnClickListener() {
                            public void onClick(DialogInterface dialog, int which) {
                                dialog.dismiss();
                            }
                        });
                alertDialog.show();
            }
        });
    }

    private void getSpecialWorkShifts(int userId) {
        HashMap<String, String> params = new HashMap<>();
        params.put("user_id", String.valueOf(userId));
        PerformNetworkRequest request = new PerformNetworkRequest(
                Api.URL_GET_SPECIAL_WORK_SHIFTS, params, this);
        request.execute();
    }

    @Override
    public void apiResponse(JSONObject response) {
        Log.d("json ", response.toString());
        try {
            JSONArray jsonArray = response.getJSONArray("special_work_shifts");
            specialWorkShiftList = new ArrayList<>();
            for (int i = 0; i < jsonArray.length(); i++) {
                JSONObject jsonObject = jsonArray.getJSONObject(i);
                int workShiftId = jsonObject.getInt("work_shift_id");
                String shiftStart = jsonObject.getString("shift_start");
                String shiftEnd = jsonObject.getString("shift_end");
                String company = jsonObject.getString("customer_name");
                WorkShift workShift = new WorkShift(workShiftId, shiftStart, shiftEnd, company);
                specialWorkShiftList.add(workShift);
            }
            if(specialWorkShiftList.size() != tempSpecialWorkShiftList.size()) {
                addWorkShiftsToList();
            }
            tempSpecialWorkShiftList = specialWorkShiftList;
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    private void addWorkShiftsToList() {

        if (specialWorkShiftList.isEmpty()) {
            linearLayout.removeAllViews();
            linearLayout.setBackgroundColor(Color.TRANSPARENT);
            TextView tvNoSpecialWorkShifts = new TextView(this);
            tvNoSpecialWorkShifts.setPadding(10, 100, 10, 10);
            tvNoSpecialWorkShifts.setTextColor(Color.WHITE);
            tvNoSpecialWorkShifts.setTextSize(18);
            tvNoSpecialWorkShifts.setGravity(Gravity.CENTER);
            tvNoSpecialWorkShifts.setText("Du har inga förfrågningar just nu");
            linearLayout.addView(tvNoSpecialWorkShifts);

        } else {
            List<String> stringList = new ArrayList<>();
            for (WorkShift workShift : specialWorkShiftList) {
                String[] splitDateTimeStart = workShift.getShiftStart().split(" ");
                String[] splitDateTimeEnd = workShift.getShiftEnd().split(" ");
                stringList.add(workShift.getCompany() + "\nDatum: " + splitDateTimeStart[0] +
                        "\nTid: " + splitDateTimeStart[1] + " - " + splitDateTimeEnd[1]);
            }
            ArrayAdapter<String> arrayAdapter = new ArrayAdapter<>(
                    this, R.layout.textview_list_layout, stringList);
            lvSpecialWorkShifts.setAdapter(arrayAdapter);
            lvSpecialWorkShifts.setSelection(arrayAdapter.getCount() - 1);
        }
    }

    private void updateWorkShift(WorkShift specialWorkShift) {
        HashMap<String, String> params = new HashMap<>();

        params.put("work_shift_id", String.valueOf(specialWorkShift.getWorkShiftId()));
        params.put("user_id", String.valueOf(userId));

        PerformNetworkRequest request = new PerformNetworkRequest(Api.URL_UPDATE_WORK_SHIFT, params, this);
        request.execute();

        deleteSpecialWorkShifts(specialWorkShift);
    }

    private void deleteSpecialWorkShifts(WorkShift workShift) {
        HashMap<String, String> params = new HashMap<>();

        params.put("work_shift_id", String.valueOf(workShift.getWorkShiftId()));

        PerformNetworkRequest request = new PerformNetworkRequest(Api.URL_DELETE_SPECIAL_WORK_SHIFTS, params, this);
        request.execute();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        stopRepeatingTask();
    }
}
